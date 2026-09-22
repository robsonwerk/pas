import React, { useState, useEffect } from 'react';
import { FiArrowLeft, FiSave, FiClipboard, FiCheckCircle } from 'react-icons/fi';
import './PlanoAcao.css';

export default function PlanoAcao({ onVoltar, visitaIdInicial = '' }) {
  const [visitas, setVisitas] = useState([]);
  const [visitaSelecionada, setVisitaSelecionada] = useState(visitaIdInicial);
  const [naoConformidades, setNaoConformidades] = useState([]);
  const [planos, setPlanos] = useState({}); // { [respostaId]: { acao_corretiva, responsavel, prazo, custo, status, data_conclusao } }
  const [loading, setLoading] = useState(false);
  const [mensagem, setMensagem] = useState(null);

  // Carregar lista de visitas
  useEffect(() => {
    const visitasMock = [
      { id: '101', data: '2026-03-20', empresa: 'Empresa Alpha' },
      { id: '102', data: '2026-03-18', empresa: 'Beta Soluções' },
      { id: '103', data: '2026-03-15', empresa: 'Indústria Gama' },
    ];
    setVisitas(visitasMock);
  }, []);

  // Buscar Não Conformidades ao selecionar a Visita
  useEffect(() => {
    if (!visitaSelecionada) {
      setNaoConformidades([]);
      setPlanos({});
      return;
    }

    setLoading(true);

    // Simulação do resultado SQL
    const ncMock = [
      {
        resposta_id: 1,
        numero: '1.2',
        requisito_descricao: 'Controle de temperatura do estoque mantido dentro dos limites estabelecidos.',
        descricao_nao_conformidade: 'Termômetro da câmara fria quebrado.',
        acao_corretiva: 'Trocar o termômetro por um novo digital',
        responsavel: 'João Silva',
        prazo: '2026-03-25',
        custo: '150.00',
        status: 'Pendente',
        data_conclusao: '',
      },
      {
        resposta_id: 2,
        numero: '2.1',
        requisito_descricao: 'Presença de extintores de incêndio com carga dentro do prazo de validade.',
        descricao_nao_conformidade: 'Extintor da recepção com carga vencida desde o mês passado.',
        acao_corretiva: 'Enviar extintor para recarga',
        responsavel: 'Maria Souza',
        prazo: '2026-03-22',
        custo: '80.00',
        status: 'Concluído',
        data_conclusao: '2026-03-21',
      },
    ];

    setNaoConformidades(ncMock);

    // Inicializar o estado dos planos editáveis
    const planosIniciais = {};
    ncMock.forEach((item) => {
      planosIniciais[item.resposta_id] = {
        acao_corretiva: item.acao_corretiva || '',
        responsavel: item.responsavel || '',
        prazo: item.prazo || '',
        custo: item.custo || '',
        status: item.status || 'Pendente',
        data_conclusao: item.data_conclusao || '',
      };
    });
    setPlanos(planosIniciais);

    setLoading(false);
  }, [visitaSelecionada]);

  // Manipulador de alterações nos campos do plano
  const handleInputChange = (respostaId, campo, valor) => {
    setPlanos((prev) => ({
      ...prev,
      [respostaId]: {
        ...prev[respostaId],
        [campo]: valor,
      },
    }));
  };

  // Salvar Plano de Ação
  const handleSubmit = (e) => {
    e.preventDefault();

    const payload = {
      visita_id: visitaSelecionada,
      planos: planos,
    };

    console.log('Salvando Plano de Ação:', payload);

    setMensagem({ tipo: 'sucesso', texto: 'Plano de Ação salvo com sucesso!' });
    setTimeout(() => setMensagem(null), 4000);
  };

  return (
    <div className="plano-container">
      {/* CABEÇALHO */}
      <div className="plano-header">
        <h2 className="plano-title">
          <FiClipboard />
          Plano de Ação
        </h2>
        {onVoltar && (
          <button className="btn-voltar" onClick={onVoltar}>
            <FiArrowLeft /> Voltar
          </button>
        )}
      </div>

      {/* MENSAGEM DE ALERTA */}
      {mensagem && (
        <div className="alert alert-success alert-custom d-flex align-items-center" role="alert">
          <FiCheckCircle className="me-2" />
          {mensagem.texto}
        </div>
      )}

      {/* FILTRO DE VISITAS */}
      <div className="filter-card">
        <div className="row align-items-end">
          <div className="col-md-12">
            <label className="filter-label">Selecione a Visita</label>
            <select
              className="form-select filter-select"
              value={visitaSelecionada}
              onChange={(e) => setVisitaSelecionada(e.target.value)}
            >
              <option value="">-- Selecione --</option>
              {visitas.map((v) => (
                <option key={v.id} value={v.id}>
                  {v.data.split('-').reverse().join('/')} - {v.empresa}
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* TABELA DE PLANO DE AÇÃO */}
      {loading ? (
        <div className="text-center my-5">
          <div className="spinner-border text-primary" role="status"></div>
          <p className="mt-2 text-muted">Carregando não conformidades...</p>
        </div>
      ) : visitaSelecionada && naoConformidades.length > 0 ? (
        <form onSubmit={handleSubmit}>
          <div className="plano-card">
            <div className="plano-card-header">
              <h5 className="mb-0">Não Conformidades Encontradas</h5>
              <button type="submit" className="btn-salvar">
                <FiSave /> Salvar Plano
              </button>
            </div>

            <div className="table-responsive">
              <table className="table table-hover align-middle plano-table">
                <thead>
                  <tr>
                    <th style={{ width: '25%' }}>Não Conformidade</th>
                    <th>Ação Corretiva</th>
                    <th>Responsável</th>
                    <th style={{ width: '130px' }}>Prazo</th>
                    <th style={{ width: '100px' }}>Custo (R$)</th>
                    <th style={{ width: '130px' }}>Status</th>
                    <th style={{ width: '140px' }}>Data Conclusão</th>
                  </tr>
                </thead>
                <tbody>
                  {naoConformidades.map((item) => {
                    const p = planos[item.resposta_id] || {};
                    const isConcluido = p.status === 'Concluído';

                    return (
                      <tr key={item.resposta_id} className={isConcluido ? 'row-concluido' : ''}>
                        <td>
                          <div style={{ fontSize: '0.85rem' }}>
                            <strong className="text-dark">Req. {item.numero}:</strong> {item.requisito_descricao}
                          </div>
                          <small className="text-danger d-block mt-1">
                            <strong>Evidência:</strong> {item.descricao_nao_conformidade}
                          </small>
                        </td>

                        {/* AÇÃO CORRETIVA */}
                        <td>
                          <input
                            type="text"
                            className="form-control input-custom"
                            value={p.acao_corretiva || ''}
                            onChange={(e) => handleInputChange(item.resposta_id, 'acao_corretiva', e.target.value)}
                            placeholder="Descreva a ação..."
                          />
                        </td>

                        {/* RESPONSÁVEL */}
                        <td>
                          <input
                            type="text"
                            className="form-control input-custom"
                            value={p.responsavel || ''}
                            onChange={(e) => handleInputChange(item.resposta_id, 'responsavel', e.target.value)}
                            placeholder="Nome..."
                          />
                        </td>

                        {/* PRAZO */}
                        <td>
                          <input
                            type="date"
                            className="form-control input-custom"
                            value={p.prazo || ''}
                            onChange={(e) => handleInputChange(item.resposta_id, 'prazo', e.target.value)}
                          />
                        </td>

                        {/* CUSTO */}
                        <td>
                          <input
                            type="number"
                            step="0.01"
                            className="form-control input-custom"
                            value={p.custo || ''}
                            onChange={(e) => handleInputChange(item.resposta_id, 'custo', e.target.value)}
                            placeholder="0,00"
                          />
                        </td>

                        {/* STATUS */}
                        <td>
                          <select
                            className={`form-select input-custom ${isConcluido ? 'select-status-concluido' : 'select-status-pendente'}`}
                            value={p.status || 'Pendente'}
                            onChange={(e) => handleInputChange(item.resposta_id, 'status', e.target.value)}
                          >
                            <option value="Pendente">Pendente</option>
                            <option value="Concluído">Concluído</option>
                          </select>
                        </td>

                        {/* DATA DE CONCLUSÃO */}
                        <td>
                          <input
                            type="date"
                            className="form-control input-custom"
                            value={p.data_conclusao || ''}
                            onChange={(e) => handleInputChange(item.resposta_id, 'data_conclusao', e.target.value)}
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
      ) : visitaSelecionada ? (
        <div className="alert alert-success text-center py-4 filter-card">
          Esta visita não possui nenhuma <strong>Não Conformidade</strong> registrada!
        </div>
      ) : (
        <div className="alert alert-info text-center py-4 filter-card">
          Por favor, selecione uma <strong>Visita</strong> acima para carregar as não conformidades e gerenciar o Plano de Ação.
        </div>
      )}
    </div>
  );
}