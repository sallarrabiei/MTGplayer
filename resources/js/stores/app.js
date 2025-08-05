import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAppStore = defineStore('app', () => {
  // State
  const isLoading = ref(false);
  const error = ref(null);
  const user = ref(null);
  
  // Actions
  function setLoading(loading) {
    isLoading.value = loading;
  }
  
  function setError(errorMessage) {
    error.value = errorMessage;
  }
  
  function clearError() {
    error.value = null;
  }
  
  function setUser(userData) {
    user.value = userData;
  }
  
  function clearUser() {
    user.value = null;
  }
  
  // Getters
  const isAuthenticated = computed(() => !!user.value);
  
  return {
    // State
    isLoading,
    error,
    user,
    
    // Actions
    setLoading,
    setError,
    clearError,
    setUser,
    clearUser,
    
    // Getters
    isAuthenticated
  };
});