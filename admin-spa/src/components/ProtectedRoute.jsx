import { useEffect } from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import useAuthStore from '../store/authStore';

export default function ProtectedRoute() {
    const { isAuthenticated, token, logout } = useAuthStore();

    useEffect(() => {
        // Double check token exists
        const storedToken = localStorage.getItem('admin_token');
        if (!storedToken && isAuthenticated) {
            // State says authenticated but no token - logout
            logout();
        }
    }, [isAuthenticated, token, logout]);

    // If not authenticated, redirect to login
    if (!isAuthenticated || !token) {
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
}
