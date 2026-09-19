// src/pages/Dashboard.jsx
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';

export function Dashboard() {
  const { usuario, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div style={{ padding: '20px', fontFamily: 'sans-serif' }}>
      <h1>Painel de Controle (Protegido)</h1>
      <p>Bem-vindo, <strong>{usuario?.nome}</strong>!</p>
      <p>Papel: {usuario?.papel_id}</p>
      
      <button onClick={handleLogout} style={{ padding: '8px 16px', cursor: 'pointer' }}>
        Sair
      </button>
    </div>
  );
}