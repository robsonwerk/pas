// src/components/ProtectedRoute.jsx
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function ProtectedRoute() {
  const { estaAutenticado } = useAuth();

  if (!estaAutenticado) {
    // Redireciona para o login mantendo o histórico limpo
    return <Navigate to="/login" replace />;
  }

  // Renderiza a rota filha protegida
  return <Outlet />;
}