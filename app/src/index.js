import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import Login from './Login';
import Dashboard from './Dashboard';
import Perfil from './Perfil';
import Empresa from './Empresa';
import Visitas from './Visitas';
import Checklist from './Checklist';
import Relatorios from './Relatorios';
import PlanoAcao from './PlanoAcao';
import './index.css';

function AppContainer() {
  const [usuario, setUsuario] = useState(null);
  const [paginaAtual, setPaginaAtual] = useState('dashboard');
  
  // 1. O useState DEVE ficar no nível superior do componente
  const [visitaSelecionadaPlano, setVisitaSelecionadaPlano] = useState('');

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

    // 2. Switch limpo, sem hooks e sem cases duplicados
    switch (paginaAtual) {
      case 'perfil':
        return <Perfil usuario={usuario} onVoltar={() => setPaginaAtual('dashboard')} />;

      case 'empresa':
        return <Empresa onVoltar={() => setPaginaAtual('dashboard')} />;

      case 'visitas':
        return <Visitas onVoltar={() => setPaginaAtual('dashboard')} />;

      case 'checklist':
        return <Checklist onVoltar={() => setPaginaAtual('dashboard')} />;

      case 'relatorios':
        return (
          <Relatorios 
            onVoltar={() => setPaginaAtual('dashboard')} 
            onNavegarPlanoAcao={(visitaId) => {
              setVisitaSelecionadaPlano(visitaId);
              setPaginaAtual('plano_acao');
            }}
          />
        );

      case 'plano_acao':
        return (
          <PlanoAcao 
            visitaIdInicial={visitaSelecionadaPlano}
            onVoltar={() => setPaginaAtual('dashboard')} 
          />
        );

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