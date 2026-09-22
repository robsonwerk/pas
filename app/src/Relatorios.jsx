import React, { useState, useEffect } from 'react';
import { FiArrowLeft, FiFileText, FiClipboard, FiList } from 'react-icons/fi';
import './Relatorios.css';

export default function Relatorios({ onVoltar, onNavegarPlanoAcao }) {
  const [checklists, setChecklists] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Simulação dos dados que viriam do banco (SQL do PHP)
    const carregarDados = () => {
      setLoading(true);
      
      const dadosMock = [
        {
          visita_id: 101,
          nome_empresa: 'Empresa Alpha',
          data_da_visita: '2026-03-20',
          total_nao_conformes: 2,
        },
        {
          visita_id: 102,
          nome_empresa: 'Beta Soluções',
          data_da_visita: '2026-03-18',
          total_nao_conformes: 0,
        },
        {
          visita_id: 103,
          nome_empresa: 'Indústria Gama',
          data_da_visita: '2026-03-15',
          total_nao_conformes: 5,
        },
      ];

      setChecklists(dadosMock);
      setLoading(false);
    };

    carregarDados();
  }, []);

  // Formatação de data (d/m/Y)
  const formatarData = (dataIso) => {
    if (!dataIso) return '';
    const [ano, mes, dia] = dataIso.split('-');
    return `${dia}/${mes}/${ano}`;
  };

  return (
    <div className="relatorios-container">
      {/* CABEÇALHO */}
      <div className="relatorios-header">
        <h2 className="relatorios-title">
          <FiFileText />
          Checklists Realizados
        </h2>
        {onVoltar && (
          <button className="btn-voltar" onClick={onVoltar}>
            <FiArrowLeft /> Voltar
          </button>
        )}
      </div>

      {/* CARD PRINCIPAL COM TABELA */}
      <div className="relatorios-card">
        <div className="relatorios-card-header">
          <h5 className="mb-0">Lista de checklists preenchidos</h5>
        </div>

        {loading ? (
          <div className="text-center my-5">
            <div className="spinner-border text-primary" role="status"></div>
            <p className="mt-2 text-muted">Carregando relatórios...</p>
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table table-hover align-middle relatorios-table">
              <thead>
                <tr>
                  <th>Empresa</th>
                  <th>Data da Visita</th>
                  <th className="text-center">Resultado</th>
                  <th className="text-center">Relatórios</th>
                </tr>
              </thead>
              <tbody>
                {checklists.length > 0 ? (
                  checklists.map((visita) => (
                    <tr key={visita.visita_id}>
                      <td className="fw-semibold text-dark">{visita.nome_empresa}</td>
                      <td>{formatarData(visita.data_da_visita)}</td>
                      <td className="text-center">
                        {visita.total_nao_conformes > 0 ? (
                          <span className="badge bg-danger badge-custom">
                            {visita.total_nao_conformes} Não Conformidade(s)
                          </span>
                        ) : (
                          <span className="badge bg-success badge-custom">
                            100% Conforme
                          </span>
                        )}
                      </td>
                      <td className="text-center">
                        <div className="d-flex justify-content-center gap-2">
                          {/* Botão Ver Checklist */}
                          <button
                            className="btn-acao-primary"
                            title="Ver Relatório do Checklist"
                            onClick={() => alert(`Abrindo relatório do checklist ID: ${visita.visita_id}`)}
                          >
                            <FiList /> Ver Checklist
                          </button>

                          {/* Botão Plano de Ação (Apenas se houver Não Conformidades) */}
                          {visita.total_nao_conformes > 0 && (
                            <button
                              className="btn-acao-success"
                              title="Criar ou Editar o Plano de Ação"
                              onClick={() => {
                                if (onNavegarPlanoAcao) {
                                  onNavegarPlanoAcao(visita.visita_id);
                                } else {
                                  alert(`Ir para Plano de Ação da visita: ${visita.visita_id}`);
                                }
                              }}
                            >
                              <FiClipboard /> Plano de Ação
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan="4" className="text-center py-4 text-muted">
                      Nenhum checklist preenchido encontrado.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}