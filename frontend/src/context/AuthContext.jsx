// src/context/AuthContext.jsx
import { createContext, useContext, useState } from 'react';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [usuario, setUsuario] = useState(() => {
    const usuarioSalvo = localStorage.getItem('@app:usuario');
    return usuarioSalvo ? JSON.parse(usuarioSalvo) : null;
  });

  const login = (dadosUsuario) => {
    setUsuario(dadosUsuario);
    localStorage.setItem('@app:usuario', JSON.stringify(dadosUsuario));
  };

  const logout = () => {
    setUsuario(null);
    localStorage.removeItem('@app:usuario');
  };

  return (
    <AuthContext.Provider value={{ usuario, login, logout, estaAutenticado: !!usuario }}>
      {children}
    </AuthContext.Provider>
  );
}

// Hook personalizado para usar a autenticação de forma simples
export function useAuth() {
  return useContext(AuthContext);
}