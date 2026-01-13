import { create } from 'zustand';

const useAuthStore = create((set) => ({
    user: null,
    token: null,
    isAuthenticated: false,

    // Initialize from localStorage
    initialize: () => {
        const token = localStorage.getItem('admin_token');
        if (token) {
            set({ token, isAuthenticated: true });
        } else {
            set({ token: null, user: null, isAuthenticated: false });
        }
    },

    login: (token, user) => {
        localStorage.setItem('admin_token', token);
        set({ token, user, isAuthenticated: true });
    },

    logout: () => {
        localStorage.removeItem('admin_token');
        set({ token: null, user: null, isAuthenticated: false });
    },

    setUser: (user) => set({ user })
}));

export default useAuthStore;
