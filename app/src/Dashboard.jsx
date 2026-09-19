import React from 'react';
import './Dashboard.css';
import { 
  FiBriefcase, 
  FiMapPin, 
  FiCheckSquare, 
  FiList, 
  FiFileText, 
  FiPower 
} from 'react-icons/fi';

export default function Dashboard({ usuario, onLogout, onNavegar }) {
  if (!usuario) return null;

  const menuItems = [
    { id: 'empresa', title: 'Empresa', icon: <FiBriefcase /> },
    { id: 'visitas', title: 'Visitas', icon: <FiMapPin /> },
    { id: 'checklist', title: 'Checklist', icon: <FiCheckSquare /> },
    { id: 'plano_acao', title: 'Plano de Ação', icon: <FiList /> },
    { id: 'relatorios', title: 'Relatórios', icon: <FiFileText /> },
  ];

  return (
    <div className="dashboard-container">
      <header className="dashboard-header">
        <img 
          src={process.env.PUBLIC_URL + '/logo-pas.png'} 
          alt="Logo PAS" 
          className="header-logo" 
        />
        <h1 className="dashboard-title">PAS - PROGRAMA ALIMENTO SEGURO</h1>
      </header>

      <main className="dashboard-grid">
        {menuItems.map((item) => (
          <button
            key={item.id}
            className="card-item"
            onClick={() => onNavegar && onNavegar(item.id)}
          >
            <div className="card-icon">{item.icon}</div>
            <span className="card-title">{item.title}</span>
          </button>
        ))}
      </main>

      <button onClick={onLogout} className="fab-logout" title="Sair">
        <FiPower />
      </button>
    </div>
  );
}