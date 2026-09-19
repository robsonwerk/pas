import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import Login from './Login';
import Dashboard from './Dashboard';
import Perfil from './Perfil';
import Empresa from './Empresa';
import './index.css';

function AppContainer() {
  const [usuario, setUsuario] = useState(null);
  const [paginaAtual, setPaginaAtual] = useState('dashboard');

  useEffect(() => {
    const usuarioSalvo = localStorage.getItem('usuario');
    if (usuarioSalvo) {
      try {
        setUsuario(JSON.parse(usuarioSalvo));
      } catch (e) {
        localStorage.removeItem('usuario');
      }
    }
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('usuario');
    setUsuario(null);
    setPaginaAtual('dashboard');
  };

  const renderPagina = () => {
    if (!usuario) {
      return <Login onLoginSucesso={(usr) => setUsuario(usr)} />;
    }

    switch (paginaAtual) {
      case 'perfil':
        return <Perfil usuario={usuario} onVoltar={() => setPaginaAtual('dashboard')} />;

      case 'empresa':
        return <Empresa onVoltar={() => setPaginaAtual('dashboard')} />;

      case 'visitas':
        return <div><h1>Tela Visitas</h1><button onClick={() => setPaginaAtual('dashboard')}>Voltar</button></div>;

      case 'checklist':
        return <div><h1>Tela Checklist</h1><button onClick={() => setPaginaAtual('dashboard')}>Voltar</button></div>;

      case 'plano_acao':
        return <div><h1>Tela Plano de Ação</h1><button onClick={() => setPaginaAtual('dashboard')}>Voltar</button></div>;

      case 'relatorios':
        return <div><h1>Tela Relatórios</h1><button onClick={() => setPaginaAtual('dashboard')}>Voltar</button></div>;

      default:
        return (
          <Dashboard 
            usuario={usuario} 
            onLogout={handleLogout} 
            onNavegar={(id) => setPaginaAtual(id)} 
          />
        );
    }
  };

  return <React.StrictMode>{renderPagina()}</React.StrictMode>;
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<AppContainer />);