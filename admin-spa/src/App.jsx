import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Locations from './pages/Locations';
import Services from './pages/Services';
import Quotes from './pages/Quotes';
import Media from './pages/Media';
import Settings from './pages/Settings';
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
            <Route path="/locations" element={<Locations />} />
            <Route path="/services" element={<Services />} />
            <Route path="/quotes" element={<Quotes />} />
            <Route path="/media" element={<Media />} />
            <Route path="/settings" element={<Settings />} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App;
