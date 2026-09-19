import React, { useState } from 'react';
import './Login.css';

export default function Login({ onLoginSucesso }) {
  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [erro, setErro] = useState('');
  const [carregando, setCarregando] = useState(false);

  const handleLogin = async (e) => {
    e.preventDefault();
    setErro('');

    if (!email || !senha) {
      setErro('Por favor, preencha todos os campos.');
      return;
    }

    try {
      setCarregando(true);

      const response = await fetch('https://pas.rn.senai.br/login_pas.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, senha }),
      });

      const textData = await response.text();
      let data;

      try {
        data = JSON.parse(textData);
      } catch (jsonErr) {
        console.error('Resposta não é JSON:', textData);
        throw new Error('O servidor retornou uma resposta inválida.');
      }

      if (data.status !== 'success' || !data.usuario) {
        throw new Error(data.mensagem || 'Erro ao realizar login.');
      }

      localStorage.setItem('usuario', JSON.stringify(data.usuario));

      if (onLoginSucesso) {
        onLoginSucesso(data.usuario);
      }
    } catch (err) {
      setErro(err.message || 'Erro ao conectar com o servidor.');
    } finally {
      setCarregando(false);
    }
  };

  return (
    <div className="login-screen">
      <div className="logo-container">
        <img 
          src={process.env.PUBLIC_URL + '/logo-pas.png'} 
          alt="PAS - Programa Alimento Seguro" 
          className="logo-image" 
        />
      </div>

      <h1 className="welcome-title">
        Bem-vindo ao<br />PAS APP
      </h1>

      {erro && <div className="login-error-badge">{erro}</div>}

      <form onSubmit={handleLogin} className="login-form">
        <div className="input-group">
          <label htmlFor="email">E-mail</label>
          <input
            type="email"
            id="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="e-mail"
            required
          />
        </div>

        <div className="input-group">
          <label htmlFor="senha">Senha</label>
          <input
            type="password"
            id="senha"
            value={senha}
            onChange={(e) => setSenha(e.target.value)}
            placeholder="senha"
            required
          />
        </div>

        <button type="submit" className="btn-outline-login" disabled={carregando}>
          {carregando ? 'Entrando...' : 'Entrar'}
        </button>
      </form>
    </div>
  );
}