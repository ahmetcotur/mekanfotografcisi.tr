import { create } from 'zustand';

const useAuthStore = create((set) => ({
    user: null,
    token: localStorage.getItem('admin_token'),
    isAuthenticated: !!localStorage.getItem('admin_token'),

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
