import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import useAuthStore from './store/authStore';

function App() {
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);

  return (
    <BrowserRouter basename="/admin">
      <Routes>
        <Route path="/" element={isAuthenticated ? <Navigate to="/dashboard" /> : <Login />} />

        <Route element={<ProtectedRoute />}>
          <Route element={<Layout />}>
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/locations" element={<div className="text-center py-12">Locations - Coming Soon</div>} />
            <Route path="/services" element={<div className="text-center py-12">Services - Coming Soon</div>} />
            <Route path="/quotes" element={<div className="text-center py-12">Quotes - Coming Soon</div>} />
            <Route path="/media" element={<div className="text-center py-12">Media - Coming Soon</div>} />
            <Route path="/settings" element={<div className="text-center py-12">Settings - Coming Soon</div>} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App;
