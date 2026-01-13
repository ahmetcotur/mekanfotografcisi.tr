import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Locations from './pages/Locations';
import Services from './pages/Services';
import Pages from './pages/Pages';
import SeoPages from './pages/SeoPages';
import Quotes from './pages/Quotes';
import Media from './pages/Media';
import Pexels from './pages/Pexels';
import PostEditor from './pages/PostEditor';
import Settings from './pages/Settings';
import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import useAuthStore from './store/authStore';

function App() {
  const { isAuthenticated, initialize } = useAuthStore();

  // Initialize auth state from localStorage on app load
  useEffect(() => {
    initialize();
  }, [initialize]);

  return (
    <BrowserRouter basename="/admin">
      <Routes>
        <Route path="/" element={isAuthenticated ? <Navigate to="/dashboard" replace /> : <Login />} />

        <Route element={<ProtectedRoute />}>
          <Route element={<Layout />}>
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/pages" element={<Pages />} />
            <Route path="/pages/new" element={<PostEditor />} />
            <Route path="/pages/edit/:id" element={<PostEditor />} />
            <Route path="/locations" element={<Locations />} />
            <Route path="/services" element={<Services />} />
            <Route path="/services/new" element={<PostEditor />} />
            <Route path="/services/edit/:id" element={<PostEditor />} />
            <Route path="/seo-pages" element={<SeoPages />} />
            <Route path="/quotes" element={<Quotes />} />
            <Route path="/media" element={<Media />} />
            <Route path="/pexels" element={<Pexels />} />
            <Route path="/settings" element={<Settings />} />
          </Route>
        </Route>

        {/* Catch all - redirect to login */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
