import React, { useState, useEffect } from 'react';
import './Visitas.css';
import { FiArrowLeft, FiCalendar, FiPlus, FiEdit3, FiTrash2, FiFileText, FiX } from 'react-icons/fi';

export default function Visitas({ onVoltar }) {
  const [visitas, setVisitas] = useState([]);
  const [empresas, setEmpresas] = useState([]);
  const [carregando, setCarregando] = useState(true);
  const [erro, setErro] = useState('');
  const [mensagem, setMensagem] = useState('');
  
  const [modalAberta, setModalAberta] = useState(false);
  const [visitaEmEdicao, setVisitaEmEdicao] = useState(null);

  const [form, setForm] = useState({
    empresa_id: '',
    data_da_visita: '',
    setor_vistoriado: '',
    observacao: ''
  });

  const API_URL = 'http://localhost/pas/get_visitas.php';

  const carregarDados = async () => {
    try {
      setCarregando(true);
      const [resVisitas, resEmpresas] = await Promise.all([
        fetch(API_URL),
        fetch(`${API_URL}?action=empresas`)
      ]);

      const dataVisitas = await resVisitas.json();
      const dataEmpresas = await resEmpresas.json();

      if (dataVisitas.status === 'success') setVisitas(dataVisitas.visitas || []);
      if (dataEmpresas.status === 'success') setEmpresas(dataEmpresas.empresas || []);
    } catch (e) {
      setErro('Erro ao conectar ao servidor backend.');
    } finally {
      setCarregando(false);
    }
  };

  useEffect(() => {
    carregarDados();
  }, []);

  const abrirModal = (visita = null) => {
    if (visita) {
      setVisitaEmEdicao(visita.id);
      setForm({
        empresa_id: visita.empresa_id,
        data_da_visita: visita.data_da_visita,
        setor_vistoriado: visita.setor_vistoriado || '',
        observacao: visita.observacao || ''
      });
    } else {
      setVisitaEmEdicao(null);
      setForm({ empresa_id: '', data_da_visita: '', setor_vistoriado: '', observacao: '' });
    }
    setModalAberta(true);
  };

  const handleSalvar = async (e) => {
    e.preventDefault();
    try {
      const payload = {
        ...form,
        ...(visitaEmEdicao && { id_para_atualizar: visitaEmEdicao })
      };

      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await res.json();
      if (result.status === 'success') {
        setMensagem(result.mensagem);
        setModalAberta(false);
        carregarDados();
        setTimeout(() => setMensagem(''), 4000);
      } else {
        alert(result.mensagem || 'Erro ao salvar visita.');
      }
    } catch (e) {
      alert('Falha de conexão ao salvar.');
    }
  };

  const handleExcluir = async (id) => {
    if (!window.confirm('Tem certeza que deseja excluir esta visita?')) return;

    try {
      const res = await fetch(`${API_URL}?delete_id=${id}`, { method: 'DELETE' });
      const result = await res.json();
      if (result.status === 'success') {
        setMensagem(result.mensagem);
        carregarDados();
        setTimeout(() => setMensagem(''), 4000);
      }
    } catch (e) {
      alert('Erro ao excluir visita.');
    }
  };

  const formatarData = (dataIso) => {
    if (!dataIso) return '-';
    const [ano, mes, dia] = dataIso.split('-');
    return `${dia}/${mes}/${ano}`;
  };

  return (
    <div className="visitas-container">
      <header className="visitas-header">
        <button onClick={onVoltar} className="btn-voltar">
          <FiArrowLeft />
        </button>
        <h1 className="header-title">Gerenciamento de Visitas</h1>
        <button onClick={() => abrirModal(null)} className="btn-novo">
          <FiPlus /> Agendar Visita
        </button>
      </header>

      <main className="visitas-content">
        {mensagem && <div className="alert-sucesso">{mensagem}</div>}
        {erro && <div className="alert-erro">{erro}</div>}

        {carregando ? (
          <p className="msg-carregando">Carregando dados...</p>
        ) : (
          <div className="tabela-card">
            <h2 className="card-secao-titulo">Visitas Agendadas</h2>
            <div className="tabela-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Empresa</th>
                    <th>Setor Vistoriado</th>
                    <th>Data da Visita</th>
                    <th style={{ textAlign: 'center' }}>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {visitas.length === 0 ? (
                    <tr>
                      <td colSpan="4" style={{ textAlign: 'center' }}>Nenhuma visita agendada.</td>
                    </tr>
                  ) : (
                    visitas.map((v) => (
                      <tr key={v.id}>
                        <td><strong>{v.nome_empresa}</strong></td>
                        <td>{v.setor_vistoriado || '-'}</td>
                        <td>{formatarData(v.data_da_visita)}</td>
                        <td>
                          <div className="acoes-btn-group">
                            <button className="btn-acao-editar" onClick={() => abrirModal(v)}>
                              <FiEdit3 /> Editar
                            </button>
                            <button className="btn-acao-excluir" onClick={() => handleExcluir(v.id)}>
                              <FiTrash2 /> Excluir
                            </button>
                            <a 
                              href={`http://localhost/pas/relatorio_checklist.php?visita_id=${v.id}`} 
                              target="_blank" 
                              rel="noreferrer" 
                              className="btn-acao-relatorio"
                            >
                              <FiFileText /> Relatório
                            </a>
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

      {/* Modal Agendar/Editar */}
      {modalAberta && (
        <div className="modal-overlay">
          <div className="modal-card">
            <div className="modal-header">
              <h3>{visitaEmEdicao ? 'Editando Visita' : 'Agendar Nova Visita'}</h3>
              <button onClick={() => setModalAberta(false)} className="btn-fechar"><FiX /></button>
            </div>

            <form onSubmit={handleSalvar} className="form-visita">
              <div className="form-group">
                <label>Empresa</label>
                <select 
                  value={form.empresa_id} 
                  onChange={(e) => setForm({ ...form, empresa_id: e.target.value })} 
                  required
                >
                  <option value="">-- Selecione uma empresa --</option>
                  {empresas.map((emp) => (
                    <option key={emp.id} value={emp.id}>{emp.nome}</option>
                  ))}
                </select>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>Data da Visita</label>
                  <input 
                    type="date" 
                    value={form.data_da_visita} 
                    onChange={(e) => setForm({ ...form, data_da_visita: e.target.value })} 
                    required 
                  />
                </div>

                <div className="form-group">
                  <label>Setor a ser Vistoriado</label>
                  <input 
                    type="text" 
                    placeholder="Ex: Cozinha, Produção..." 
                    value={form.setor_vistoriado} 
                    onChange={(e) => setForm({ ...form, setor_vistoriado: e.target.value })} 
                  />
                </div>
              </div>

              <div className="form-group">
                <label>Observação (Opcional)</label>
                <textarea 
                  rows="3" 
                  value={form.observacao} 
                  onChange={(e) => setForm({ ...form, observacao: e.target.value })} 
                />
              </div>

              <div className="form-actions">
                <button type="submit" className="btn-salvar-visita">
                  {visitaEmEdicao ? 'Atualizar Visita' : 'Agendar Visita'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}