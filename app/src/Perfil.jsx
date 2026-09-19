import React from 'react';
import './Perfil.css';
import { FiArrowLeft, FiUser, FiEdit2 } from 'react-icons/fi';

export default function Perfil({ usuario, onVoltar }) {
  if (!usuario) return null;

  return (
    <div className="perfil-container">
      {/* Barra Superior */}
      <nav className="perfil-nav">
        <button onClick={onVoltar} className="btn-voltar" title="Voltar">
          <FiArrowLeft />
        </button>
        <span className="perfil-nav-title">Perfil</span>
      </nav>

      {/* Seção Superior com Degradê e Foto */}
      <header className="perfil-header-card">
        <div className="avatar-circle">
          <FiUser className="avatar-icon" />
        </div>
        <h2 className="user-name">{usuario.nome || 'Usuário PAS'}</h2>
        <p className="user-id">ID: {usuario.id || 'N/A'}</p>
      </header>

      {/* Detalhes Adicionais do Usuário */}
      <main className="perfil-body">
        <div className="info-field">
          <span className="info-label">E-mail</span>
          <span className="info-value">{usuario.email}</span>
        </div>

        {usuario.empresa_id && (
          <div className="info-field">
            <span className="info-label">Empresa ID</span>
            <span className="info-value">{usuario.empresa_id}</span>
          </div>
        )}

        {usuario.consultoria_id && (
          <div className="info-field">
            <span className="info-label">Consultoria ID</span>
            <span className="info-value">{usuario.consultoria_id}</span>
          </div>
        )}
      </main>

      {/* Botão Flutuante de Edição */}
      <button className="fab-edit" title="Editar Perfil" onClick={() => alert('Editar perfil em breve!')}>
        <FiEdit2 />
      </button>
    </div>
  );
}