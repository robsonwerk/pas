import React, { useState, useEffect } from 'react';
import './Empresa.css';
import { FiArrowLeft, FiPlus, FiEdit3, FiTrash2, FiX } from 'react-icons/fi';

export default function Empresa({ onVoltar }) {
  const [empresas, setEmpresas] = useState([]);
  const [carregando, setCarregando] = useState(true);
  const [erroApi, setErroApi] = useState('');
  const [modalAberta, setModalAberta] = useState(false);
  const [modoModal, setModoModal] = useState('novo'); // 'novo' ou 'editar'
  
  const [form, setForm] = useState({
    id: '',
    nome: '',
    setor_id: '',
    razao_social: '',
    nome_fantasia: '',
    endereco: '',
    contato_nome: '',
    telefone: '',
    localidade: '',
    uf: '',
    cnpj: ''
  });

  const carregarEmpresas = async () => {
    try {
      setCarregando(true);
      setErroApi('');
     const res = await fetch('http://localhost/pas/get_empresas.php?action=list');
      
      if (!res.ok) throw new Error('Falha na resposta do servidor.');
      
      const data = await res.json();
      if (data.status === 'success') {
        setEmpresas(data.empresas || []);
      } else {
        setErroApi(data.mensagem || 'Erro ao carregar dados.');
      }
    } catch (e) {
      setErroApi('Não foi possível conectar ao servidor backend.');
    } finally {
      setCarregando(false);
    }
  };

  useEffect(() => {
    carregarEmpresas();
  }, []);

  const abrirModal = (empresa = null, modo = 'novo') => {
    setModoModal(modo);
    if (empresa) {
      setForm({ ...empresa });
    } else {
      setForm({
        id: '', nome: '', setor_id: '', razao_social: '', nome_fantasia: '',
        endereco: '', contato_nome: '', telefone: '', localidade: '', uf: '', cnpj: ''
      });
    }
    setModalAberta(true);
  };

  return (
    <div className="empresa-container">
      <header className="empresa-header">
        <button onClick={onVoltar} className="btn-voltar">
          <FiArrowLeft />
        </button>
        <h1 className="header-title">Gerenciamento de Empresas</h1>
        <button onClick={() => abrirModal(null, 'novo')} className="btn-novo">
          <FiPlus /> Nova Empresa
        </button>
      </header>

      <main className="empresa-content">
        {carregando && <p className="msg-status">Carregando empresas...</p>}
        {erroApi && <p className="msg-status erro">{erroApi}</p>}

        {!carregando && !erroApi && (
          <div className="tabela-card">
            <h2 className="card-secao-titulo">Empresas Cadastradas</h2>
            <div className="tabela-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Setor</th>
                    <th>UF</th>
                    <th>CNPJ</th>
                    <th style={{ textAlign: 'center' }}>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {empresas.length === 0 ? (
                    <tr>
                      <td colSpan="5" style={{ textAlign: 'center' }}>Nenhuma empresa encontrada.</td>
                    </tr>
                  ) : (
                    empresas.map((emp) => (
                      <tr key={emp.id}>
                        <td><strong>{emp.nome || emp.nome_fantasia}</strong></td>
                        <td>{emp.setor_nome || emp.setor_id || 'Agronegócio'}</td>
                        <td>{emp.uf || 'RN'}</td>
                        <td>{emp.cnpj || '-'}</td>
                        <td>
                          <div className="acoes-btn-group">
                            <button className="btn-acao-editar" onClick={() => abrirModal(emp, 'editar')}>
                              <FiEdit3 /> Editar
                            </button>
                            <button className="btn-acao-excluir" onClick={() => alert('Excluir empresa')}>
                              <FiTrash2 /> Excluir
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </main>

      {/* Modal Formulário Completo */}
      {modalAberta && (
        <div className="modal-overlay">
          <div className="modal-card">
            <div className="modal-header">
              <h3>{modoModal === 'novo' ? 'Cadastrar Nova Empresa' : 'Editar Empresa'}</h3>
              <button onClick={() => setModalAberta(false)} className="btn-fechar"><FiX /></button>
            </div>

            <form onSubmit={(e) => { e.preventDefault(); setModalAberta(false); }} className="form-empresa-grid">
              <div className="form-group span-1">
                <label>Nome de Identificação (Curto)</label>
                <input type="text" value={form.nome} onChange={(e) => setForm({...form, nome: e.target.value})} required />
              </div>

              <div className="form-group span-1">
                <label>Setor</label>
                <select value={form.setor_id} onChange={(e) => setForm({...form, setor_id: e.target.value})}>
                  <option value="">-- Selecione um setor --</option>
                  <option value="1">Agronegócio</option>
                  <option value="2">Alimentos</option>
                  <option value="3">Indústria</option>
                </select>
              </div>

              <div className="form-group span-1">
                <label>Razão Social</label>
                <input type="text" value={form.razao_social} onChange={(e) => setForm({...form, razao_social: e.target.value})} />
              </div>

              <div className="form-group span-1">
                <label>Nome Fantasia</label>
                <input type="text" value={form.nome_fantasia} onChange={(e) => setForm({...form, nome_fantasia: e.target.value})} />
              </div>

              <div className="form-group span-2">
                <label>Endereço Completo</label>
                <input type="text" value={form.endereco} onChange={(e) => setForm({...form, endereco: e.target.value})} />
              </div>

              <div className="form-group span-1">
                <label>Pessoa de Contato</label>
                <input type="text" value={form.contato_nome} onChange={(e) => setForm({...form, contato_nome: e.target.value})} />
              </div>

              <div className="form-group span-1">
                <label>Telefone</label>
                <input type="text" placeholder="(00) 00000-0000" value={form.telefone} onChange={(e) => setForm({...form, telefone: e.target.value})} />
              </div>

              <div className="form-group span-1">
                <label>Cidade/Localidade</label>
                <input type="text" value={form.localidade} onChange={(e) => setForm({...form, localidade: e.target.value})} />
              </div>

              <div className="form-group span-col-small">
                <label>UF</label>
                <input type="text" maxLength="2" value={form.uf} onChange={(e) => setForm({...form, uf: e.target.value})} />
              </div>

              <div className="form-group span-1">
                <label>CNPJ</label>
                <input type="text" value={form.cnpj} onChange={(e) => setForm({...form, cnpj: e.target.value})} />
              </div>

              <div className="form-actions span-2">
                <button type="submit" className="btn-salvar-empresa">Salvar Empresa</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}