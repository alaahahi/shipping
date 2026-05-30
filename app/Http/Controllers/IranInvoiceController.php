<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\IranInvoice;
use App\Models\IranInvoiceItem;
use App\Models\IranInvoiceCar;
use App\Models\IranInvoiceCarrier;
use App\Models\IranInvoiceConsignee;
use App\Models\IranInvoiceAttachment;
use App\Models\IranInvoiceTransfer;
use App\Models\SystemConfig;

/**
 * Standalone Iran invoices module.
 *
 * Fully isolated from the legacy accounting system: it never touches the
 * car / wallets / transactions / transfers tables, and never calls the
 * AccountingCacheService. Everything lives under the iran_invoice_* tables.
 */
class IranInvoiceController extends Controller
{
    // ---------------------------------------------------------------------
    // Inertia pages
    // ---------------------------------------------------------------------

    public function index()
    {
        return Inertia::render('IranInvoices/Index', [
            'owner_id' => Auth::user()->owner_id,
        ]);
    }

    public function create()
    {
        return Inertia::render('IranInvoices/Form', [
            'owner_id' => Auth::user()->owner_id,
            'invoice_id' => null,
        ]);
    }

    public function edit($id)
    {
        $invoice = IranInvoice::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$invoice) {
            return redirect()->route('iranInvoices.index');
        }

        return Inertia::render('IranInvoices/Form', [
            'owner_id' => Auth::user()->owner_id,
            'invoice_id' => (int) $id,
        ]);
    }

    // ---------------------------------------------------------------------
    // Invoices
    // ---------------------------------------------------------------------

    public function getInvoices(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $q = $request->get('q', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');
        $archived = $request->get('archived', 0);
        $limit = (int) $request->get('limit', 25);

        $query = IranInvoice::with(['carrier', 'consignee', 'attachments'])
            ->withCount('items')
            ->where('owner_id', $owner_id)
            ->where('is_archived', $archived ? 1 : 0)
            ->orderBy('id', 'desc');

        if ($from && $to && $from != '0' && $to != '0') {
            $query->whereBetween('invoice_date', [$from, $to]);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('invoice_no', 'LIKE', '%' . $q . '%')
                    ->orWhere('carrier_name', 'LIKE', '%' . $q . '%')
                    ->orWhere('consignee_name', 'LIKE', '%' . $q . '%')
                    ->orWhereHas('items', function ($itemQuery) use ($q) {
                        $itemQuery->where('chassis_no', 'LIKE', '%' . $q . '%');
                    });
            });
        }

        return Response::json($query->paginate($limit), 200);
    }

    public function getInvoice($id)
    {
        $invoice = IranInvoice::with(['items.attachments', 'carrier', 'consignee', 'attachments'])
            ->where('owner_id', Auth::user()->owner_id)
            ->find($id);

        if (!$invoice) {
            return Response::json(['error' => 'Not found'], 404);
        }

        return Response::json($invoice, 200);
    }

    public function storeInvoice(Request $request)
    {
        Validator::make($request->all(), [
            'items' => 'required|array|min:1',
        ])->validate();

        $owner_id = Auth::user()->owner_id;

        $invoice = DB::transaction(function () use ($request, $owner_id) {
            $invoice = IranInvoice::create($this->invoiceAttributes($request, $owner_id, true));
            $this->syncItems($invoice, $request->get('items', []));
            $this->applyTotalPrice($request, $invoice);

            return $invoice;
        });

        return Response::json($invoice->load('items'), 200);
    }

    public function getNextInvoiceNo()
    {
        $owner_id = Auth::user()->owner_id;

        return Response::json([
            'invoice_no' => $this->nextInvoiceNo($owner_id, false),
        ], 200);
    }

    public function updateInvoice(Request $request, $id)
    {
        $owner_id = Auth::user()->owner_id;
        $invoice = IranInvoice::where('owner_id', $owner_id)->find($id);

        if (!$invoice) {
            return Response::json(['error' => 'Not found'], 404);
        }

        Validator::make($request->all(), [
            'items' => 'required|array|min:1',
        ])->validate();

        DB::transaction(function () use ($request, $owner_id, $invoice) {
            $invoice->update($this->invoiceAttributes($request, $owner_id, false, $invoice));
            $this->syncItems($invoice, $request->get('items', []));
            $this->applyTotalPrice($request, $invoice);
        });

        return Response::json($invoice->fresh(['items.attachments']), 200);
    }

    public function archiveInvoice(Request $request, $id)
    {
        $invoice = IranInvoice::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$invoice) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $invoice->update([
            'is_archived' => true,
            'archived_at' => Carbon::now(),
        ]);

        return Response::json(['success' => true], 200);
    }

    public function unarchiveInvoice(Request $request, $id)
    {
        $invoice = IranInvoice::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$invoice) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $invoice->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        return Response::json(['success' => true], 200);
    }

    public function destroyInvoice($id)
    {
        $invoice = IranInvoice::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$invoice) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $invoice->delete();

        return Response::json(['success' => true], 200);
    }

    public function printInvoice($id)
    {
        $invoice = IranInvoice::with(['items', 'carrier', 'consignee'])
            ->where('owner_id', Auth::user()->owner_id)
            ->find($id);

        if (!$invoice) {
            abort(404);
        }

        $this->ensureVerificationToken($invoice);

        $config = SystemConfig::first();
        $verificationUrl = route('iranInvoices.verify', $invoice->verification_token);

        return view('iran_invoices.print', compact('invoice', 'config', 'verificationUrl'));
    }

    public function verifyInvoice($token)
    {
        $invoice = IranInvoice::with(['items', 'carrier', 'consignee'])
            ->where('verification_token', $token)
            ->firstOrFail();

        $config = SystemConfig::first();
        $verificationUrl = route('iranInvoices.verify', $invoice->verification_token);

        return view('iran_invoices.verify', compact('invoice', 'config', 'verificationUrl'));
    }

    // ---------------------------------------------------------------------
    // Cars registry
    // ---------------------------------------------------------------------

    public function getCars(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $q = $request->get('q', '');
        $limit = (int) $request->get('limit', 25);

        $query = IranInvoiceCar::with('attachments')
            ->where('owner_id', $owner_id)
            ->orderBy('id', 'desc');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('chassis_no', 'LIKE', '%' . $q . '%')
                    ->orWhere('make', 'LIKE', '%' . $q . '%')
                    ->orWhere('model', 'LIKE', '%' . $q . '%');
            });
        }

        return Response::json($query->paginate($limit), 200);
    }

    public function storeCar(Request $request)
    {
        $car = IranInvoiceCar::create($this->carAttributes($request));

        return Response::json($car->load('attachments'), 200);
    }

    public function updateCar(Request $request, $id)
    {
        $car = IranInvoiceCar::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$car) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $car->update($this->carAttributes($request));

        return Response::json($car->fresh('attachments'), 200);
    }

    public function destroyCar($id)
    {
        $car = IranInvoiceCar::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$car) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $car->delete();

        return Response::json(['success' => true], 200);
    }

    // ---------------------------------------------------------------------
    // Attachments (invoices or cars)
    // ---------------------------------------------------------------------

    public function uploadAttachment(Request $request)
    {
        Validator::make($request->all(), [
            'type' => 'required|in:invoice,car,item',
            'id' => 'required|integer',
            'file' => 'required|file|max:10240',
        ])->validate();

        $owner_id = Auth::user()->owner_id;
        $model = $this->resolveAttachable($request->get('type'), $request->get('id'), $owner_id);

        if (!$model) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $path1 = public_path('uploads');
        $path2 = public_path('uploadsResized');
        if (!file_exists($path1)) {
            mkdir($path1, 0777, true);
        }
        if (!file_exists($path2)) {
            mkdir($path2, 0777, true);
        }

        $file = $request->file('file');
        $originalName = trim($file->getClientOriginalName());
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        // Safe disk name only (no spaces) — original_name kept for display.
        $name = uniqid('iran_') . '.' . $extension;
        $file->move($path1, $name);

        // Only image files get a resized thumbnail; PDFs and others are stored as-is.
        $isImage = @getimagesize(public_path('uploads/' . $name)) !== false;
        if ($isImage) {
            try {
                $image = Image::make(public_path('uploads/' . $name));
                $image->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save(public_path('uploadsResized/' . $name));
            } catch (\Throwable $e) {
                // Keep original upload even if thumbnail generation fails.
            }
        }

        $attachment = $model->attachments()->create([
            'file_name' => $name,
            'original_name' => $originalName,
            'owner_id' => $owner_id,
        ]);

        return Response::json($attachment, 200);
    }

    public function deleteAttachment(Request $request)
    {
        $id = $request->get('id');
        $attachment = IranInvoiceAttachment::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$attachment) {
            return Response::json(['error' => 'Not found'], 404);
        }

        File::delete(public_path('uploads/' . $attachment->file_name));
        File::delete(public_path('uploadsResized/' . $attachment->file_name));
        $attachment->delete();

        return Response::json(['success' => true], 200);
    }

    // ---------------------------------------------------------------------
    // Transfers (module-only)
    // ---------------------------------------------------------------------

    public function getTransfers(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $q = $request->get('q', '');
        $from = $request->get('from', '');
        $to = $request->get('to', '');
        $archived = $request->get('archived', 0);
        $limit = (int) $request->get('limit', 25);

        $query = IranInvoiceTransfer::with('invoice:id,invoice_no')
            ->where('owner_id', $owner_id)
            ->where('is_archived', $archived ? 1 : 0)
            ->orderBy('id', 'desc');

        if ($from && $to && $from != '0' && $to != '0') {
            $query->whereBetween('transfer_date', [$from, $to]);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_no', 'LIKE', '%' . $q . '%')
                    ->orWhere('from_text', 'LIKE', '%' . $q . '%')
                    ->orWhere('to_text', 'LIKE', '%' . $q . '%');
            });
        }

        return Response::json($query->paginate($limit), 200);
    }

    public function storeTransfer(Request $request)
    {
        $transfer = IranInvoiceTransfer::create($this->transferAttributes($request));

        return Response::json($transfer, 200);
    }

    public function updateTransfer(Request $request, $id)
    {
        $transfer = IranInvoiceTransfer::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$transfer) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $transfer->update($this->transferAttributes($request));

        return Response::json($transfer->fresh(), 200);
    }

    public function archiveTransfer(Request $request, $id)
    {
        $transfer = IranInvoiceTransfer::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$transfer) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $transfer->update([
            'is_archived' => !$transfer->is_archived,
            'archived_at' => $transfer->is_archived ? null : Carbon::now(),
        ]);

        return Response::json(['success' => true], 200);
    }

    public function destroyTransfer($id)
    {
        $transfer = IranInvoiceTransfer::where('owner_id', Auth::user()->owner_id)->find($id);

        if (!$transfer) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $transfer->delete();

        return Response::json(['success' => true], 200);
    }

    // ---------------------------------------------------------------------
    // Lookups
    // ---------------------------------------------------------------------

    public function getCarriers(Request $request)
    {
        $carriers = IranInvoiceCarrier::where('owner_id', Auth::user()->owner_id)
            ->orderBy('name')
            ->get();

        return Response::json($carriers, 200);
    }

    public function storeCarrier(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ])->validate();

        $carrier = IranInvoiceCarrier::create([
            'name' => $request->get('name'),
            'phone' => $request->get('phone'),
            'address' => $request->get('address'),
            'notes' => $request->get('notes'),
            'owner_id' => Auth::user()->owner_id,
        ]);

        return Response::json($carrier, 200);
    }

    public function getConsignees(Request $request)
    {
        $consignees = IranInvoiceConsignee::where('owner_id', Auth::user()->owner_id)
            ->orderBy('name')
            ->get();

        return Response::json($consignees, 200);
    }

    public function storeConsignee(Request $request)
    {
        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ])->validate();

        $consignee = IranInvoiceConsignee::create([
            'name' => $request->get('name'),
            'phone' => $request->get('phone'),
            'address' => $request->get('address'),
            'notes' => $request->get('notes'),
            'owner_id' => Auth::user()->owner_id,
        ]);

        return Response::json($consignee, 200);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function invoiceAttributes(Request $request, $owner_id, bool $isNew, ?IranInvoice $existing = null): array
    {
        $carrierName = $request->get('carrier_name');
        if (!$carrierName && $request->get('carrier_id')) {
            $carrierName = optional(IranInvoiceCarrier::find($request->get('carrier_id')))->name;
        }

        $consigneeName = $request->get('consignee_name');
        if (!$consigneeName && $request->get('consignee_id')) {
            $consigneeName = optional(IranInvoiceConsignee::find($request->get('consignee_id')))->name;
        }

        $attributes = [
            'invoice_no' => $this->resolveInvoiceNo($request, $owner_id, $isNew, $existing),
            'invoice_date' => $request->get('invoice_date') ?: Carbon::now()->format('Y-m-d'),
            'carrier_id' => $request->get('carrier_id'),
            'consignee_id' => $request->get('consignee_id'),
            'carrier_name' => $carrierName,
            'consignee_name' => $consigneeName,
            'destination' => $request->get('destination'),
            'notes' => $request->get('notes'),
            'currency' => $request->get('currency', 'USD'),
        ];

        if ($isNew) {
            $attributes['owner_id'] = $owner_id;
            $attributes['created_by'] = Auth::id();
        }

        return $attributes;
    }

    private function resolveInvoiceNo(Request $request, $owner_id, bool $isNew, ?IranInvoice $existing = null): string
    {
        $manual = trim((string) $request->input('invoice_no', ''));

        if ($manual !== '') {
            return $manual;
        }

        if (!$isNew && $existing && !empty($existing->invoice_no)) {
            return $existing->invoice_no;
        }

        return $this->generateInvoiceNo($owner_id);
    }

    private function syncItems(IranInvoice $invoice, array $items): void
    {
        $keepIds = [];
        $sort = 0;

        foreach ($items as $item) {
            $unitPrice = $item['unit_price'] ?? null;
            if ($unitPrice === '' || $unitPrice === false) {
                $unitPrice = null;
            }

            $attributes = [
                'car_id' => $item['car_id'] ?? null,
                'chassis_no' => $item['chassis_no'] ?? null,
                'make' => $item['make'] ?? null,
                'model' => $item['model'] ?? null,
                'year' => $item['year'] ?? null,
                'color' => $item['color'] ?? null,
                'weight' => $item['weight'] ?? null,
                'unit_price' => $unitPrice,
                'notes' => $item['notes'] ?? null,
                'sort_order' => $sort++,
            ];

            if (!empty($item['id'])) {
                $existing = $invoice->items()->where('id', $item['id'])->first();
                if ($existing) {
                    $existing->update($attributes);
                    $keepIds[] = (int) $existing->id;
                    continue;
                }
            }

            $created = $invoice->items()->create($attributes);
            $keepIds[] = (int) $created->id;
        }

        $removed = $invoice->items()->whereNotIn('id', $keepIds)->get();
        foreach ($removed as $row) {
            $this->deleteAttachmentFiles($row->attachments);
            $row->attachments()->delete();
            $row->delete();
        }
    }

    /**
     * Total price stays optional: explicit value wins, otherwise sum line
     * prices only when at least one line carries a price, else leave null.
     */
    private function applyTotalPrice(Request $request, IranInvoice $invoice): void
    {
        $total = $request->get('total_price');

        if ($total === null || $total === '') {
            $lineTotal = $invoice->items()->whereNotNull('unit_price')->sum('unit_price');
            $hasPricedLine = $invoice->items()->whereNotNull('unit_price')->exists();
            $total = $hasPricedLine ? $lineTotal : null;
        }

        $invoice->update(['total_price' => $total]);
    }

    private function generateInvoiceNo($owner_id): string
    {
        return $this->nextInvoiceNo($owner_id, true);
    }

    private function nextInvoiceNo($owner_id, bool $lock): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = 'IR-' . $year . '-';

        $query = IranInvoice::withTrashed()
            ->where('invoice_no', 'like', $prefix . '%');

        if ($owner_id !== null) {
            $query->where('owner_id', $owner_id);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        $max = 0;
        foreach ($query->pluck('invoice_no') as $invoiceNo) {
            if (preg_match('/-(\d+)$/', (string) $invoiceNo, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $prefix . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
    }

    private function carAttributes(Request $request): array
    {
        return [
            'chassis_no' => $request->get('chassis_no'),
            'make' => $request->get('make'),
            'model' => $request->get('model'),
            'year' => $request->get('year'),
            'color' => $request->get('color'),
            'weight' => $request->get('weight'),
            'notes' => $request->get('notes'),
            'carrier_id' => $request->get('carrier_id'),
            'consignee_id' => $request->get('consignee_id'),
            'owner_id' => Auth::user()->owner_id,
            'created_by' => Auth::id(),
        ];
    }

    private function transferAttributes(Request $request): array
    {
        return [
            'transfer_date' => $request->get('transfer_date') ?: Carbon::now()->format('Y-m-d'),
            'amount' => $request->get('amount', 0),
            'currency' => $request->get('currency', 'USD'),
            'reference_no' => $request->get('reference_no'),
            'from_text' => $request->get('from_text'),
            'to_text' => $request->get('to_text'),
            'notes' => $request->get('notes'),
            'invoice_id' => $request->get('invoice_id'),
            'owner_id' => Auth::user()->owner_id,
            'created_by' => Auth::id(),
        ];
    }

    private function resolveAttachable(string $type, $id, $owner_id)
    {
        if ($type === 'invoice') {
            return IranInvoice::where('owner_id', $owner_id)->find($id);
        }

        if ($type === 'item') {
            return IranInvoiceItem::whereHas('invoice', function ($query) use ($owner_id) {
                $query->where('owner_id', $owner_id);
            })->find($id);
        }

        return IranInvoiceCar::where('owner_id', $owner_id)->find($id);
    }

    private function deleteAttachmentFiles($attachments): void
    {
        foreach ($attachments as $attachment) {
            File::delete(public_path('uploads/' . $attachment->file_name));
            File::delete(public_path('uploadsResized/' . $attachment->file_name));
        }
    }

    private function ensureVerificationToken(IranInvoice $invoice): void
    {
        if (!empty($invoice->verification_token)) {
            return;
        }

        $invoice->verification_token = Str::uuid()->toString();
        $invoice->save();
    }
}
