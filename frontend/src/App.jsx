import { useState } from 'react';

// Ajuste para o endereço/porta correto do seu servidor PHP
const API_LOGIN_URL = 'http://localhost/pas/login.php'; 

export default function App() {
  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [erro, setErro] = useState('');
  const [carregando, setCarregando] = useState(false);
  
  // Recupera o usuário salvo no navegador se existir
  const [usuario, setUsuario] = useState(() => {
    const usuarioSalvo = localStorage.getItem('@app:usuario');
    return usuarioSalvo ? JSON.parse(usuarioSalvo) : null;
  });

  const handleLogin = async (e) => {
    e.preventDefault();
    setErro('');
    setCarregando(true);

    try {
      const response = await fetch(API_LOGIN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, senha }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.erro || 'Falha na autenticação');
      }

      // Salva os dados no estado e no LocalStorage
      setUsuario(data.usuario);
      localStorage.setItem('@app:usuario', JSON.stringify(data.usuario));

    } catch (err) {
      setErro(err.message);
    } finally {
      setCarregando(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('@app:usuario');
    setUsuario(null);
  };

  // --- TELA DE PAINEL (Usuário Logado) ---
  if (usuario) {
    return (
      <div style={{ padding: '20px', fontFamily: 'sans-serif', maxWidth: '500px', margin: '50px auto' }}>
        <h2>Bem-vindo, {usuario.nome}!</h2>
        <p><strong>E-mail:</strong> {usuario.email}</p>
        <p><strong>Papel (ID):</strong> {usuario.papel_id}</p>
        <p><strong>Empresa ID:</strong> {usuario.empresa_id ?? 'N/A'}</p>
        <p><strong>Consultoria ID:</strong> {usuario.consultoria_id ?? 'N/A'}</p>
        
        <button 
          onClick={handleLogout}
          style={{ padding: '10px 15px', backgroundColor: '#dc3545', color: '#fff', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
        >
          Sair da Conta
        </button>
      </div>
    );
  }

  // --- TELA DE LOGIN ---
  return (
    <div style={{ padding: '20px', fontFamily: 'sans-serif', maxWidth: '380px', margin: '50px auto', border: '1px solid #ccc', borderRadius: '8px' }}>
      <h2>Acesso ao Sistema</h2>

      {erro && (
        <div style={{ padding: '10px', marginBottom: '15px', backgroundColor: '#f8d7da', color: '#721c24', borderRadius: '4px' }}>
          {erro}
        </div>
      )}

      <form onSubmit={handleLogin} style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
        <div>
          <label style={{ display: 'block', marginBottom: '4px' }}>E-mail</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            style={{ width: '100%', padding: '8px', boxSizing: 'border-box' }}
          />
        </div>

        <div>
          <label style={{ display: 'block', marginBottom: '4px' }}>Senha</label>
          <input
            type="password"
            value={senha}
            onChange={(e) => setSenha(e.target.value)}
            required
            style={{ width: '100%', padding: '8px', boxSizing: 'border-box' }}
          />
        </div>

        <button
          type="submit"
          disabled={carregando}
          style={{ padding: '10px', backgroundColor: '#0d6efd', color: '#fff', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
        >
          {carregando ? 'Entrando...' : 'Entrar'}
        </button>
      </form>
    </div>
  );
}