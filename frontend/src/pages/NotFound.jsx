// src/pages/NotFound.jsx
import { Link } from 'react-router-dom';

export function NotFound() {
  return (
    <div style={{ padding: '20px', textAlign: 'center' }}>
      <h1>404 - Página Não Encontrada</h1>
      <Link to="/dashboard">Voltar para o início</Link>
    </div>
  );
}