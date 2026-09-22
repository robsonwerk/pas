import React, { useState, useEffect } from 'react';
import { FiArrowLeft, FiSave, FiCheckCircle, FiAlertCircle } from 'react-icons/fi';
import './Checklist.css'; // <--- Adicione esta linha

export default function Checklist({ onVoltar }) {
  // --- ESTADOS DA APLICAÇÃO ---
  const [visitas, setVisitas] = useState([]);
  const [etapas, setEtapas] = useState([]);

  const [visitaSelecionada, setVisitaSelecionada] = useState('');
  const [etapaSelecionada, setEtapaSelecionada] = useState('');

  const [requisitos, setRequisitos] = useState([]);
  const [respostas, setRespostas] = useState({}); // { [requisitoId]: { criticidade, conformidade, descricao } }

  const [loading, setLoading] = useState(false);
  const [mensagem, setMensagem] = useState(null);

  // --- CARREGAMENTO INICIAL DE VISITAS E ETAPAS ---
  useEffect(() => {
    const visitasFicticias = [
      { id: 1, empresa: 'Empresa Alpha', data: '2026-03-20' },
      { id: 2, empresa: 'Beta Soluções', data: '2026-03-18' },
    ];
    const etapasFicticias = [
      { id: 1, nome: 'Recebimento de Matéria-Prima' },
      { id: 2, nome: 'Armazenamento e Estocagem' },
      { id: 3, nome: 'Manipulação e Preparo' },
    ];

    setVisitas(visitasFicticias);
    setEtapas(etapasFicticias);
  }, []);

  // --- BUSCA DE REQUISITOS AO MUDAR OS SELETORES ---
  useEffect(() => {
    if (!etapaSelecionada) {
      setRequisitos([]);
      return;
    }

    setLoading(true);

    const requisitosExemplo = [
      { id: 101, numero: '1.1', descricao: 'Área externa limpa, organizada e isenta de focos de contaminação.', criticidade_padrao: 'Não Crítico' },
      { id: 102, numero: '1.2', descricao: 'Controle de temperatura do estoque mantido dentro dos limites estabelecidos.', criticidade_padrao: 'Crítico' },
      { id: 103, numero: '1.3', descricao: 'Uniformes dos manipuladores limpos e em bom estado de conservação.', criticidade_padrao: 'Não Crítico' },
    ];

    setRequisitos(requisitosExemplo);

    if (visitaSelecionada) {
      const respostasSalvas = {
        101: { criticidade: 'Não Crítico', conformidade: 'Conforme', descricao: '' },
        102: { criticidade: 'Crítico', conformidade: 'Não Conforme', descricao: 'Termômetro da câmara fria quebrado.' },
      };
      setRespostas(respostasSalvas);
    } else {
      const respostasIniciais = {};
      requisitosExemplo.forEach((req) => {
        respostasIniciais[req.id] = {
          criticidade: req.criticidade_padrao,
          conformidade: '',
          descricao: '',
        };
      });
      setRespostas(respostasIniciais);
    }

    setLoading(false);
  }, [visitaSelecionada, etapaSelecionada]);

  // --- MANIPULADORES DE ALTERAÇÃO DOS CAMPOS ---
  const handleRespostaChange = (reqId, campo, valor) => {
    setRespostas((prev) => ({
      ...prev,
      [reqId]: {
        ...prev[reqId],
        [campo]: valor,
      },
    }));
  };

  // Redimensionamento automático do TextArea
  const handleAutoExpand = (e) => {
    e.target.style.height = 'auto';
    e.target.style.height = e.target.scrollHeight + 'px';
  };

  // --- CORES DINÂMICAS DE CONFORMIDADE ---
  const getClasseConformidade = (conf) => {
    switch (conf) {
      case 'Conforme':
        return 'bg-success text-white fw-bold';
      case 'Não Conforme':
        return 'bg-danger text-white fw-bold';
      case 'Não Aplicável':
        return 'bg-secondary text-white fw-bold';
      default:
        return 'bg-light text-dark';
    }
  };

  // --- ENVIO DO FORMULÁRIO (ONLINE / OFFLINE) ---
  const handleSubmit = (e) => {
    e.preventDefault();

    if (!visitaSelecionada || !etapaSelecionada) {
      alert('Por favor, selecione a Visita e a Etapa antes de salvar.');
      return;
    }

    const payload = {
      visita_id: visitaSelecionada,
      etapa_id: etapaSelecionada,
      respostas: respostas,
    };

    if (navigator.onLine) {
      console.log('Enviando dados para o servidor:', payload);
      setMensagem({ tipo: 'sucesso', texto: 'Checklist salvo com sucesso no servidor!' });
    } else {
      console.log('Sem conexão. Salvando localmente:', payload);
      const offlineData = JSON.parse(localStorage.getItem('checklist_offline') || '[]');
      offlineData.push(payload);
      localStorage.setItem('checklist_offline', JSON.stringify(offlineData));

      setMensagem({ tipo: 'aviso', texto: 'Você está offline. Checklist salvo localmente!' });
    }

    setTimeout(() => setMensagem(null), 4000);
  };

 return (
    <div className="checklist-container">
      {/* CABEÇALHO */}
      <div className="checklist-header">
        <h2 className="checklist-title">
          <FiCheckCircle />
          Checklist de Verificação
        </h2>
        {onVoltar && (
          <button className="btn-voltar" onClick={onVoltar}>
            <FiArrowLeft /> Voltar
          </button>
        )}
      </div>

      {/* MENSAGEM DE ALERTA */}
      {mensagem && (
        <div className={`alert alert-custom ${mensagem.tipo === 'sucesso' ? 'alert-success' : 'alert-warning'} d-flex align-items-center`} role="alert">
          <FiAlertCircle className="me-2" />
          {mensagem.texto}
        </div>
      )}

      {/* PAINEL DE SELEÇÃO DE FILTROS */}
      <div className="filter-card">
        <div className="row g-3">
          <div className="col-md-6">
            <label className="filter-label">Visita</label>
            <select
              className="form-select filter-select"
              value={visitaSelecionada}
              onChange={(e) => setVisitaSelecionada(e.target.value)}
            >
              <option value="">-- Selecione a Visita --</option>
              {visitas.map((v) => (
                <option key={v.id} value={v.id}>
                  {v.data} - {v.empresa}
                </option>
              ))}
            </select>
          </div>

          <div className="col-md-6">
            <label className="filter-label">Etapa</label>
            <select
              className="form-select filter-select"
              value={etapaSelecionada}
              onChange={(e) => setEtapaSelecionada(e.target.value)}
            >
              <option value="">-- Selecione a Etapa --</option>
              {etapas.map((e) => (
                <option key={e.id} value={e.id}>
                  {e.nome}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* TABELA DE REQUISITOS */}
      {loading ? (
        <div className="text-center my-5">
          <div className="spinner-border text-primary" role="status"></div>
          <p className="mt-2 text-muted">Carregando itens de verificação...</p>
        </div>
      ) : requisitos.length > 0 ? (
        <form onSubmit={handleSubmit}>
          <div className="table-card">
            <div className="table-card-header">
              <h5 className="mb-0">Itens de Verificação</h5>
              <button type="submit" className="btn-salvar">
                <FiSave /> Salvar Tudo
              </button>
            </div>

            <div className="table-responsive">
              <table className="table table-hover align-middle checklist-table">
                <thead>
                  <tr>
                    <th style={{ width: '60px' }}>Nº</th>
                    <th>Requisito</th>
                    <th style={{ width: '160px' }}>Criticidade</th>
                    <th style={{ width: '180px' }}>Conformidade</th>
                    <th>Descrição da Não Conformidade</th>
                  </tr>
                </thead>
                <tbody>
                  {requisitos.map((req) => {
                    const resp = respostas[req.id] || {
                      criticidade: req.criticidade_padrao,
                      conformidade: '',
                      descricao: '',
                    };

                    const isNaoConforme = resp.conformidade === 'Não Conforme';

                    return (
                      <tr key={req.id}>
                        <td className="fw-bold text-secondary">{req.numero}</td>
                        <td>
                          <span className="text-dark fw-medium" style={{ fontSize: '0.9rem' }}>
                            {req.descricao}
                          </span>
                        </td>

                        {/* SELECT CRITICIDADE */}
                        <td>
                          <select
                            className="form-select form-select-sm select-sm-custom"
                            value={resp.criticidade}
                            onChange={(e) => handleRespostaChange(req.id, 'criticidade', e.target.value)}
                          >
                            <option value="Crítico">Crítico</option>
                            <option value="Não Crítico">Não Crítico</option>
                          </select>
                        </td>

                        {/* SELECT CONFORMIDADE */}
                        <td>
                          <select
                            className={`form-select form-select-sm select-sm-custom ${getClasseConformidade(resp.conformidade)}`}
                            value={resp.conformidade}
                            required
                            onChange={(e) => handleRespostaChange(req.id, 'conformidade', e.target.value)}
                          >
                            <option value="" className="bg-white text-dark">
                              Selecione...
                            </option>
                            <option value="Conforme" className="bg-white text-dark">
                              Conforme
                            </option>
                            <option value="Não Conforme" className="bg-white text-dark">
                              Não Conforme
                            </option>
                            <option value="Não Aplicável" className="bg-white text-dark">
                              Não Aplicável
                            </option>
                          </select>
                        </td>

                        {/* DESCRICAO / JUSTIFICATIVA */}
                        <td>
                          <textarea
                            className={`form-control textarea-custom ${isNaoConforme ? 'textarea-nc' : ''}`}
                            style={{
                              minHeight: '38px',
                              resize: 'none',
                              overflow: 'hidden',
                              transition: 'all 0.2s ease',
                            }}
                            value={resp.descricao}
                            required={isNaoConforme}
                            rows={1}
                            placeholder={
                              isNaoConforme
                                ? 'Descreva a não conformidade encontrada...'
                                : 'Observações (opcional)...'
                            }
                            onInput={handleAutoExpand}
                            onChange={(e) => handleRespostaChange(req.id, 'descricao', e.target.value)}
                          />
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </form>
      ) : (
        <div className="alert alert-info text-center py-4 filter-card">
          Por favor, selecione uma <strong>Etapa</strong> no menu acima para visualizar o checklist correspondente.
        </div>
      )}
    </div>
  );
}