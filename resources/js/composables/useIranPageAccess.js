import { computed, ref, watch } from 'vue';
import axios from 'axios';

const TOKEN_STORAGE_KEY = 'iran_page_token';

function readTokenFromUrl() {
  if (typeof window === 'undefined') {
    return '';
  }
  try {
    return new URLSearchParams(window.location.search).get('token') || '';
  } catch (e) {
    return '';
  }
}

function storedToken() {
  if (typeof window === 'undefined') {
    return '';
  }
  return sessionStorage.getItem(TOKEN_STORAGE_KEY) || '';
}

function persistToken(token) {
  if (typeof window === 'undefined' || !token) {
    return;
  }
  sessionStorage.setItem(TOKEN_STORAGE_KEY, token);
}

export function useIranPageAccess(props) {
  const unlocked = ref(!!props.iran_unlocked);
  const canDelete = ref(!!props.iran_can_delete);
  const isAdmin = ref(!!(props.iran_is_admin ?? props.iran_can_delete));
  const pin = ref('');
  const pinError = ref('');
  const unlocking = ref(false);

  const urlToken = readTokenFromUrl();
  if (urlToken) {
    persistToken(urlToken);
  }
  const token = ref(urlToken || storedToken());

  const iranAxios = axios.create({
    withCredentials: true,
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
  });

  iranAxios.interceptors.request.use((config) => {
    if (token.value) {
      config.headers = config.headers || {};
      config.headers['X-Iran-Token'] = token.value;
    }
    return config;
  });

  const submitPin = async () => {
    const digits = String(pin.value ?? '').replace(/\D/g, '');
    if (digits.length !== 6 || unlocking.value) {
      return;
    }

    unlocking.value = true;
    pinError.value = '';
    try {
      const headers = {};
      if (token.value) {
        headers['X-Iran-Token'] = token.value;
      }
      const response = await axios.post(
        '/api/iran/unlock',
        { pin: digits },
        {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            ...headers,
          },
        }
      );
      canDelete.value = !!response.data?.can_delete;
      isAdmin.value = !!(response.data?.is_admin ?? response.data?.can_delete);
      unlocked.value = true;
      pin.value = '';
    } catch (error) {
      const message =
        error.response?.data?.errors?.pin?.[0] ||
        error.response?.data?.message ||
        'رمز PIN غير صحيح';
      pinError.value = message;
      pin.value = '';
    } finally {
      unlocking.value = false;
    }
  };

  watch(pin, (value) => {
    const digits = String(value ?? '').replace(/\D/g, '');
    if (digits.length === 6) {
      submitPin();
    }
  });

  return {
    unlocked,
    canDelete: computed(() => canDelete.value),
    isAdmin: computed(() => isAdmin.value),
    pin,
    pinError,
    unlocking,
    iranAxios,
    submitPin,
  };
}
