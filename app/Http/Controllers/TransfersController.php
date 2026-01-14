<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AccountingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use App\Models\Transfers;
use App\Models\ConnectedSystem;
use App\Models\User;
use App\Models\Car;
use App\Models\Wallet;
use App\Models\UserType;
use App\Models\ExpensesType;
use Illuminate\Support\Facades\DB;
use App\Models\Transactions;
use App\Models\Expenses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ExternalTransferService;



use Carbon\Carbon;

use Inertia\Inertia;

class TransfersController extends Controller
{
    protected $externalTransferService;

    public function __construct(AccountingController $accountingController, ExternalTransferService $externalTransferService)
    {
    $this->accountingController = $accountingController;
    $this->externalTransferService = $externalTransferService;
    $this->url = env('FRONTEND_URL');
    $this->userAdmin =  UserType::where('name', 'admin')->first()->id;
    $this->userClient =  UserType::where('name', 'client')->first()->id;
    $this->userAccount =  UserType::where('name', 'account')->first()->id;

    $this->mainAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','main@account.com');
    $this->inAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','in@account.com');
    $this->outAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','out@account.com');
    $this->debtAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','debt@account.com');
    $this->transfersAccount= User::with('wallet')->where('type_id', $this->userAccount)->where('email','transfers@account.com');
    $this->outSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-out');
    $this->debtSupplier= User::with('wallet')->where('type_id', $this->userAccount)->where('email','supplier-debt');
    $this->onlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts');
    $this->onlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-dinar');
    $this->debtOnlineContracts= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debt');
    $this->debtOnlineContractsDinar= User::with('wallet')->where('type_id', $this->userAccount)->where('email','online-contracts-debit-dinar');
    $this->howler= User::with('wallet')->where('type_id', $this->userAccount)->where('email','howler');
    $this->shippingCoc= User::with('wallet')->where('type_id', $this->userAccount)->where('email','shipping-coc');
    $this->border= User::with('wallet')->where('type_id', $this->userAccount)->where('email','border');
    $this->iran= User::with('wallet')->where('type_id', $this->userAccount)->where('email','iran');
    $this->dubai= User::with('wallet')->where('type_id', $this->userAccount)->where('email','dubai');
    $this->mainBox= User::with('wallet')->where('type_id', $this->userAccount)->where('email','mainBox@account.com');
    }

    public function index(Request $request)
    {
        $transfers = Transfers::get();
        return Response::json($transfers, 200);    
    }

    public function addTransfers(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $amount = $request->input('amount', $request->get('amount', ''));
        $sender_note = $request->input('sender_note', $request->get('sender_note', ''));
        $external_system_id = $request->input('external_system_id');
        
        $maxNo = Transfers::max('no') ?? 0;
        $no = $maxNo + 1;
        
        // إذا كان تحويل خارجي
        if ($external_system_id) {
            // التحقق من وجود النظام الخارجي
            $targetSystem = ConnectedSystem::where('id', $external_system_id)
                ->where('is_active', true)
                ->first();

            if (!$targetSystem) {
                return Response::json(['error' => 'النظام الخارجي غير موجود أو غير مفعل'], 404);
            }

            // إنشاء تحويل خارجي
            $transfer = Transfers::create([
                'no' => $no,
                'user_id' => Auth::user()->id,
                'stauts' => 'قيد الإرسال',
                'sender_id' => $this->mainBox->where('owner_id', $owner_id)->first()->id,
                'amount' => $amount,
                'sender_note' => $sender_note,
                'is_external' => true,
                'external_system_id' => $external_system_id,
                'external_system_domain' => $targetSystem->domain,
            ]);

            // إرسال التحويل إلى النظام الخارجي
            $result = $this->externalTransferService->sendTransfer($transfer, $targetSystem);

            if ($result['success']) {
                return Response::json([
                    'message' => 'تم إرسال التحويل بنجاح',
                    'transfer' => $transfer->fresh()
                ], 200);
            } else {
                // تحديث حالة التحويل إلى فشل
                $transfer->update(['stauts' => 'فشل الإرسال']);
                return Response::json([
                    'error' => 'فشل إرسال التحويل',
                    'details' => $result['error']
                ], 500);
            }
        }
        
        // تحويل محلي عادي
        $tran = Transfers::create([
            'no' => $no,
            'user_id' => Auth::user()->id,
            'stauts' => 'قيد التسليم',
            'sender_id' => $this->mainBox->where('owner_id', $owner_id)->first()->id,
            'amount' => $amount,
            'sender_note' => $sender_note,
        ]);
        
        return Response::json('ok', 200);    
    }
    public function confirmTransfers(Request $request){
        $owner_id=Auth::user()->owner_id;
        $transfer_fee=$request->inputValue??0;
        $receiver_note=$request->receiver;
        $transfer=Transfers::find($request->id);
        if($transfer){
            $transfer->update(['fee'=>$transfer_fee,'receiver_id'=>$this->mainBox->where('owner_id',$owner_id)->first()->id,'receiver_note'=>$receiver_note, 'stauts'=>'تم الأستلام',]);
            $desc=' تحويل من فرع كركوك مبلغ '.$transfer->amount.' '.$transfer->sender_note.' '.$transfer->receiver_note.' '.'أجور التحويل '.$transfer->fee.' المبلغ الصافي '.$transfer->amount-$transfer->fee.' دولار ';
            $this->accountingController->decreaseWallet($transfer->amount,$desc,$transfer->sender_id,$transfer->sender_id,'App\Models\User');
            $this->accountingController->increaseWallet($transfer->amount-$transfer->fee,$desc,$transfer->receiver_id,$transfer->receiver_id,'App\Models\User');
            
            // إذا كان تحويل خارجي وارد (ليس مرسل)، إرسال تأكيد للنظام المرسل
            if($transfer->is_external && $transfer->external_system_domain && $transfer->stauts == 'تم الأستلام'){
                // البحث عن النظام المرسل
                $sourceSystem = ConnectedSystem::where('domain', $transfer->external_system_domain)
                    ->where('is_active', true)
                    ->first();
                
                if($sourceSystem){
                    $this->externalTransferService->sendTransferConfirmation($transfer, $sourceSystem);
                }
            }
            
            // إعادة تحميل البيانات المحدثة
            $transfer->refresh();
            return Response::json($transfer, 200);    
        }else{
            return Response::json('transfer not found', 405);    
        }
    }
    public function cancelTransfers(Request $request){
            $transfer_id=$request->id;
            $transfer=Transfers::find($transfer_id);
            $transfer->delete();
            return Response::json('delete done', 200);    
    }
    
    public function archiveTransfer(Request $request){
        $transfer_id = $request->id;
        $transfer = Transfers::find($transfer_id);
        if($transfer){
            $transfer->update(['is_archived' => true]);
            // إعادة تحميل البيانات المحدثة
            $transfer->refresh();
            return Response::json(['message' => 'تم أرشفة التحويل بنجاح', 'transfer' => $transfer], 200);    
        }else{
            return Response::json(['message' => 'التحويل غير موجود'], 404);    
        }
    }
    
    public function unarchiveTransfer(Request $request){
        $transfer_id = $request->id;
        $transfer = Transfers::find($transfer_id);
        if($transfer){
            $transfer->update(['is_archived' => false]);
            // إعادة تحميل البيانات المحدثة
            $transfer->refresh();
            return Response::json(['message' => 'تم إعادة التحويل للسجل بنجاح', 'transfer' => $transfer], 200);    
        }else{
            return Response::json(['message' => 'التحويل غير موجود'], 404);    
        }
    }

    /**
     * إرسال تحويل إلى نظام خارجي
     */
    public function sendExternalTransfer(Request $request)
    {
        $owner_id = Auth::user()->owner_id;
        $external_system_id = $request->external_system_id;
        $amount = $request->amount;
        $sender_note = $request->sender_note ?? '';
        $note = $request->note ?? '';

        // التحقق من وجود النظام الخارجي
        $targetSystem = ConnectedSystem::where('id', $external_system_id)
            ->where('is_active', true)
            ->first();

        if (!$targetSystem) {
            return Response::json(['error' => 'النظام الخارجي غير موجود أو غير مفعل'], 404);
        }

        // إنشاء تحويل محلي
        $maxNo = Transfers::max('no') ?? 0;
        $no = $maxNo + 1;
        
        $transfer = Transfers::create([
            'no' => $no,
            'user_id' => Auth::user()->id,
            'stauts' => 'قيد الإرسال',
            'sender_id' => $this->mainBox->where('owner_id', $owner_id)->first()->id,
            'amount' => $amount,
            'sender_note' => $sender_note,
            'note' => $note,
            'is_external' => true,
            'external_system_id' => $external_system_id,
            'external_system_domain' => $targetSystem->domain,
        ]);

        // إرسال التحويل إلى النظام الخارجي
        $result = $this->externalTransferService->sendTransfer($transfer, $targetSystem);

        if ($result['success']) {
            return Response::json([
                'message' => 'تم إرسال التحويل بنجاح',
                'transfer' => $transfer->fresh()
            ], 200);
        } else {
            // تحديث حالة التحويل إلى فشل
            $transfer->update(['stauts' => 'فشل الإرسال']);
            return Response::json([
                'error' => 'فشل إرسال التحويل',
                'details' => $result['error']
            ], 500);
        }
    }

    /**
     * استقبال تحويل من نظام خارجي
     */
    public function receiveExternalTransfer(Request $request)
    {
        // التحقق من البيانات المطلوبة
        $request->validate([
            'amount' => 'required|numeric',
            'sender_system_domain' => 'required|string',
            'transfer_no' => 'required',
        ]);
        
        $amount = $request->amount;
        $sender_note = $request->sender_note ?? '';
        $sender_system_domain = $request->sender_system_domain;
        $transfer_no = $request->transfer_no;

        // البحث عن النظام المرسل في قاعدة البيانات
        $sourceSystem = ConnectedSystem::where('domain', $sender_system_domain)
            ->where('is_active', true)
            ->first();

        // الحصول على owner_id من النظام (يمكن استخدام owner_id افتراضي أو من إعدادات النظام)
        $owner_id = 1; // يمكن تعديله حسب الحاجة
        
        // إنشاء تحويل محلي
        $maxNo = Transfers::max('no') ?? 0;
        $no = $maxNo + 1;
        
        $mainBoxUser = $this->mainBox->where('owner_id', $owner_id)->first();
        
        $transfer = Transfers::create([
            'no' => $no,
            'user_id' => $mainBoxUser->user_id ?? 1,
            'stauts' => 'قيد التسليم',
            'sender_id' => $mainBoxUser->id ?? null,
            'amount' => $amount,
            'sender_note' => $sender_note,
            'is_external' => true,
            'external_system_id' => $sourceSystem->id ?? null,
            'external_system_domain' => $sender_system_domain,
            'external_transfer_id' => $transfer_no,
        ]);

        return Response::json([
            'message' => 'تم استقبال التحويل بنجاح',
            'transfer' => $transfer
        ], 200);
    }

    /**
     * تأكيد تحويل خارجي (يتم استدعاؤه من النظام المستقبل لإعلام النظام المرسل بالتأكيد)
     * هذا endpoint يتم استدعاؤه من النظام المستقبل بعد تأكيد التحويل محلياً
     */
    public function confirmExternalTransfer(Request $request)
    {
        $request->validate([
            'external_transfer_id' => 'required',
        ]);
        
        $external_transfer_id = $request->external_transfer_id; // هذا هو رقم التحويل في النظام المرسل (no)
        $fee = $request->fee ?? 0;
        $receiver_note = $request->receiver_note ?? '';
        
        // البحث عن التحويل المرسل في النظام المحلي
        // external_transfer_id هو رقم التحويل (no) الذي أرسلناه
        $transfer = Transfers::where('no', $external_transfer_id)
            ->where('is_external', true)
            ->whereIn('stauts', ['قيد الإرسال', 'قيد التسليم'])
            ->first();

        if (!$transfer) {
            return Response::json(['error' => 'التحويل غير موجود'], 404);
        }

        // الحصول على owner_id من التحويل
        $senderUser = User::find($transfer->sender_id);
        if (!$senderUser) {
            return Response::json(['error' => 'المستخدم المرسل غير موجود'], 404);
        }
        $owner_id = $senderUser->owner_id;
        $mainBoxUser = $this->mainBox->where('owner_id', $owner_id)->first();
        
        // تحديث التحويل
        $transfer->update([
            'fee' => $fee,
            'receiver_id' => $mainBoxUser->id ?? $transfer->receiver_id,
            'receiver_note' => $receiver_note,
            'stauts' => 'تم الأستلام'
        ]);

        // تحديث الحسابات في النظام المرسل - سحب المبلغ من الصندوق
        $desc = ' تحويل إلى نظام خارجي مبلغ ' . $transfer->amount . ' ' . $transfer->sender_note . ' ' . $transfer->receiver_note . ' ' . 'أجور التحويل ' . $transfer->fee . ' المبلغ الصافي ' . ($transfer->amount - $transfer->fee) . ' دولار ';
        if ($transfer->sender_id) {
            $this->accountingController->decreaseWallet($transfer->amount, $desc, $transfer->sender_id, $transfer->sender_id, 'App\Models\User');
        }

        $transfer->refresh();
        
        return Response::json([
            'message' => 'تم تأكيد التحويل بنجاح',
            'transfer' => $transfer
        ], 200);
    }

    /**
     * جلب قائمة الأنظمة المتصلة
     */
    public function getConnectedSystems()
    {
        $systems = ConnectedSystem::where('is_active', true)->get();
        return Response::json($systems, 200);
    }

    /**
     * إضافة نظام متصل جديد
     */
    public function storeConnectedSystem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|url',
            'api_key' => 'required|string',
        ]);

        $system = ConnectedSystem::create([
            'name' => $request->name,
            'domain' => rtrim($request->domain, '/'), // إزالة / من النهاية
            'api_key' => $request->api_key,
            'is_active' => $request->is_active ?? true,
        ]);

        return Response::json([
            'message' => 'تم إضافة النظام بنجاح',
            'system' => $system
        ], 200);
    }

    /**
     * تحديث نظام متصل
     */
    public function updateConnectedSystem(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|url',
            'api_key' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $system = ConnectedSystem::find($id);
        if (!$system) {
            return Response::json(['error' => 'النظام غير موجود'], 404);
        }

        $updateData = [];
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('domain')) $updateData['domain'] = rtrim($request->domain, '/');
        if ($request->has('api_key')) $updateData['api_key'] = $request->api_key;
        if ($request->has('is_active')) $updateData['is_active'] = $request->is_active;

        $system->update($updateData);

        return Response::json([
            'message' => 'تم تحديث النظام بنجاح',
            'system' => $system->fresh()
        ], 200);
    }

    /**
     * حذف نظام متصل
     */
    public function deleteConnectedSystem($id)
    {
        $system = ConnectedSystem::find($id);
        if (!$system) {
            return Response::json(['error' => 'النظام غير موجود'], 404);
        }

        $system->delete();

        return Response::json([
            'message' => 'تم حذف النظام بنجاح'
        ], 200);
    }

    /**
     * جلب جميع الأنظمة المتصلة (بما فيها المعطلة)
     */
    public function getAllConnectedSystems()
    {
        $systems = ConnectedSystem::all();
        return Response::json($systems, 200);
    }

    /**
     * API: الحصول على التحويلات المعلقة الموجهة لنظام معين (يتم استدعاؤه من النظام المستقبل)
     * هذا endpoint يتم استدعاؤه من النظام المستقبل للاستعلام عن التحويلات المعلقة الموجهة إليه
     */
    public function getPendingTransfers(Request $request)
    {
        $request->validate([
            'receiver_system_domain' => 'required|string',
        ]);

        $receiverSystemDomain = rtrim($request->receiver_system_domain, '/');
        $currentSystemDomain = rtrim(config('app.url'), '/');

        Log::info('getPendingTransfers called', [
            'current_system' => $currentSystemDomain,
            'receiver_system_domain' => $receiverSystemDomain
        ]);

        // التحقق من أن النظام المستقبل موجود في الأنظمة المتصلة
        $receiverSystem = ConnectedSystem::where('domain', $receiverSystemDomain)
            ->where('is_active', true)
            ->first();

        if (!$receiverSystem) {
            Log::warning('Receiver system not found', [
                'receiver_system_domain' => $receiverSystemDomain
            ]);
            return Response::json([
                'success' => false,
                'error' => 'النظام المستقبل غير موجود أو غير مفعل'
            ], 404);
        }

        Log::info('Receiver system found', [
            'receiver_system_id' => $receiverSystem->id,
            'receiver_system_name' => $receiverSystem->name
        ]);

        // جلب التحويلات المعلقة الموجهة للنظام المستقبل
        $pendingTransfers = Transfers::where('is_external', true)
            ->where('external_system_domain', $receiverSystemDomain)
            ->whereIn('stauts', ['قيد الإرسال', 'قيد التسليم'])
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Pending transfers found', [
            'count' => $pendingTransfers->count(),
            'transfers' => $pendingTransfers->map(function($t) {
                return [
                    'id' => $t->id,
                    'no' => $t->no,
                    'amount' => $t->amount,
                    'stauts' => $t->stauts,
                    'external_system_domain' => $t->external_system_domain,
                    'external_transfer_id' => $t->external_transfer_id
                ];
            })->toArray()
        ]);

        return Response::json([
            'success' => true,
            'transfers' => $pendingTransfers
        ], 200);
    }

    /**
     * التحقق من التحويلات المعلقة من جميع الأنظمة المتصلة وإدخالها في النظام المحلي
     * يتم استدعاؤه من الواجهة عند فتح نافذة التحويلات من فروع أخرى
     * يستدعي get-pending-transfers من كل نظام متصل للاستعلام عن التحويلات المعلقة الموجهة للنظام الحالي
     */
    public function checkPendingExternalTransfers(Request $request)
    {
        $owner_id = Auth::user()->owner_id ?? 1;
        $currentSystemUrl = config('app.url');
        
        Log::info('checkPendingExternalTransfers called', [
            'current_system' => $currentSystemUrl,
            'owner_id' => $owner_id
        ]);
        
        $connectedSystems = ConnectedSystem::where('is_active', true)->get();
        
        Log::info('Connected systems found', [
            'count' => $connectedSystems->count(),
            'systems' => $connectedSystems->map(function($sys) {
                return [
                    'id' => $sys->id,
                    'name' => $sys->name,
                    'domain' => $sys->domain
                ];
            })->toArray()
        ]);
        
        $totalReceived = 0;
        $errors = [];

        foreach ($connectedSystems as $system) {
            $targetUrl = rtrim($system->domain, '/') . '/api/get-pending-transfers';
            
            Log::info('Calling external system', [
                'system_name' => $system->name,
                'system_id' => $system->id,
                'target_url' => $targetUrl,
                'receiver_system_domain' => $currentSystemUrl
            ]);
            
            try {
                // استدعاء endpoint في النظام المرسل للاستعلام عن التحويلات المعلقة
                $response = Http::timeout(10)
                    ->withHeaders([
                        'API-Key' => $system->api_key,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($targetUrl, [
                        'receiver_system_domain' => $currentSystemUrl
                    ]);

                Log::info('Response received from external system', [
                    'system_name' => $system->name,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $pendingTransfers = $responseData['transfers'] ?? [];

                    Log::info('Pending transfers from external system', [
                        'system_name' => $system->name,
                        'transfers_count' => count($pendingTransfers)
                    ]);

                    foreach ($pendingTransfers as $pendingTransfer) {
                        // التحقق من أن التحويل غير موجود مسبقاً (تجنب التكرار)
                        $existingTransfer = Transfers::where('is_external', true)
                            ->where('external_system_domain', $system->domain)
                            ->where('external_transfer_id', $pendingTransfer['no'])
                            ->first();

                        if (!$existingTransfer) {
                            // إنشاء تحويل محلي
                            $maxNo = Transfers::max('no') ?? 0;
                            $no = $maxNo + 1;

                            $mainBoxUser = $this->mainBox->where('owner_id', $owner_id)->first();

                            Transfers::create([
                                'no' => $no,
                                'user_id' => $mainBoxUser->user_id ?? 1,
                                'stauts' => 'قيد التسليم',
                                'sender_id' => $mainBoxUser->id ?? null,
                                'amount' => $pendingTransfer['amount'],
                                'sender_note' => $pendingTransfer['sender_note'] ?? '',
                                'is_external' => true,
                                'external_system_id' => $system->id,
                                'external_system_domain' => $system->domain,
                                'external_transfer_id' => $pendingTransfer['no'],
                            ]);

                            $totalReceived++;
                            
                            Log::info('Transfer created', [
                                'transfer_no' => $no,
                                'external_transfer_no' => $pendingTransfer['no'],
                                'system_name' => $system->name
                            ]);
                        }
                    }
                } else {
                    $errorMsg = 'فشل الاتصال: ' . $response->status();
                    $errors[] = [
                        'system' => $system->name,
                        'error' => $errorMsg
                    ];
                    
                    Log::warning('Failed to get pending transfers from external system', [
                        'system_name' => $system->name,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                $errors[] = [
                    'system' => $system->name,
                    'error' => $errorMsg
                ];
                
                Log::error('Exception when calling external system', [
                    'system_name' => $system->name,
                    'target_url' => $targetUrl,
                    'error' => $errorMsg,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $result = [
            'success' => true,
            'total_received' => $totalReceived,
            'errors' => $errors
        ];
        
        Log::info('checkPendingExternalTransfers completed', $result);

        return Response::json($result, 200);
    }

    /**
     * التحقق من الاتصال بالنظام الخارجي
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'domain' => 'required|string',
            'api_key' => 'required|string',
        ]);

        $domain = rtrim($request->domain, '/');
        $apiKey = $request->api_key;

        try {
            // محاولة إرسال طلب اختباري للتحقق من الاتصال
            // نستخدم POST إلى /api/receive-transfer مع بيانات اختبارية
            // إذا استجاب النظام بـ 422 (validation error) أو 400، فهذا يعني أن الـ endpoint موجود والاتصال يعمل
            $response = Http::timeout(10)
                ->withHeaders([
                    'API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($domain . '/api/receive-transfer', [
                    'amount' => 1,
                    'sender_system_domain' => env('APP_URL'),
                    'transfer_no' => 'test-connection-' . time()
                ]);

            $statusCode = $response->status();
            
            // 200 = نجاح
            // 422 أو 400 = endpoint موجود ولكن البيانات غير صحيحة (وهذا جيد - يعني الاتصال يعمل و API key صحيح)
            // 401 = API key خاطئ
            if ($statusCode === 200) {
                return Response::json([
                    'success' => true,
                    'message' => 'تم التحقق من الاتصال بنجاح'
                ], 200);
            } elseif ($statusCode === 422 || $statusCode === 400) {
                // validation error يعني أن الـ endpoint موجود والاتصال يعمل
                return Response::json([
                    'success' => true,
                    'message' => 'تم التحقق من الاتصال بنجاح'
                ], 200);
            } elseif ($statusCode === 401) {
                return Response::json([
                    'success' => false,
                    'message' => 'فشل التحقق: API Key غير صحيح'
                ], 200);
            } else {
                return Response::json([
                    'success' => false,
                    'message' => 'فشل الاتصال: كود الحالة ' . $statusCode
                ], 200);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return Response::json([
                'success' => false,
                'message' => 'فشل الاتصال: لا يمكن الوصول إلى النظام الخارجي. تأكد من صحة الدومين والاتصال بالإنترنت'
            ], 200);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage()
            ], 200);
        }
    }
}
