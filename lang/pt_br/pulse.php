<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * pulse.php
 *
 * @package   mod_pulse
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['allowchange'] = 'Permitir que o aluno altere a resposta';
$string['allowchange_help'] = 'Quando ativado, uma nova resposta substitui a resposta atual do aluno. O plugin mantém somente a resposta vigente, sem histórico de alterações.';
$string['anonymous'] = 'Respostas anônimas';
$string['anonymous_help'] = 'Quando ativado, o professor vê somente os resultados agregados. O userid não é gravado na resposta; o Moodle mantém apenas um identificador pseudonimizado por atividade para impedir respostas duplicadas. Administradores com acesso direto ao banco e ao código do site não devem tratar esse mecanismo como anonimização criptográfica absoluta.';
$string['anonymousmodecannotchange'] = 'O modo anônimo não pode ser alterado depois que a atividade já recebeu respostas.';
$string['anonymousreportnotice'] = 'Modo anônimo: o relatório apresenta somente dados agregados.';
$string['anonymousstudentnotice'] = 'Este pulso está configurado como anônimo para o professor.';
$string['backtoactivity'] = 'Voltar à atividade';
$string['cannotvote'] = 'Você pode visualizar esta atividade, mas não possui permissão para responder.';
$string['changeprompt'] = 'Você já respondeu. Se escolher outra opção e enviar, sua resposta atual será substituída.';
$string['chartbar'] = 'Barras';
$string['chartline'] = 'Linha';
$string['chartpie'] = 'Pizza';
$string['charttype'] = 'Tipo de gráfico';
$string['defaultchart'] = 'Gráfico padrão do relatório';
$string['defaultquestion'] = 'Como está sua compreensão deste conteúdo?';
$string['eventreportviewed'] = 'Relatório do pulso visualizado';
$string['eventvotesubmitted'] = 'Resposta ao pulso enviada';
$string['identifiedreportnotice'] = 'Modo identificado: as respostas ficam vinculadas ao usuário no banco de dados, mas este painel exibe somente a distribuição agregada.';
$string['invalidcharttype'] = 'Tipo de gráfico inválido.';
$string['invalidresponse'] = 'Resposta inválida.';
$string['livereport'] = 'Relatório em tempo real';
$string['missinganonsalt'] = 'Não foi possível gerar o identificador anônimo desta atividade.';
$string['modulename'] = 'Pulso da turma';
$string['modulenameplural'] = 'Pulsos da turma';
$string['nopulses'] = 'Não há atividades Pulso da turma neste curso.';
$string['openlivereport'] = 'Abrir relatório em tempo real';
$string['pluginadministration'] = 'Administração do Pulso da turma';
$string['pluginname'] = 'Pulso da turma';
$string['privacy:metadata:pulse_votes'] = 'Armazena a resposta atual de um participante ao Pulso da turma.';
$string['privacy:metadata:pulse_votes:respondenthash'] = 'Identificador pseudonimizado específico da atividade usado para manter uma resposta atual por participante.';
$string['privacy:metadata:pulse_votes:response'] = 'Nível de compreensão informado pelo participante.';
$string['privacy:metadata:pulse_votes:timecreated'] = 'Data e hora em que a resposta foi criada.';
$string['privacy:metadata:pulse_votes:timemodified'] = 'Data e hora da última alteração da resposta.';
$string['privacy:metadata:pulse_votes:userid'] = 'ID do usuário quando a atividade não é anônima; zero quando o modo anônimo está ativo.';
$string['pulse:addinstance'] = 'Adicionar uma nova atividade Pulso da turma';
$string['pulse:view'] = 'Visualizar uma atividade Pulso da turma';
$string['pulse:viewreport'] = 'Visualizar o relatório agregado do Pulso da turma';
$string['pulse:vote'] = 'Responder a uma atividade Pulso da turma';
$string['pulsename'] = 'Nome do pulso';
$string['question'] = 'Pergunta';
$string['response1'] = '😕 Não entendi';
$string['response1short'] = 'Não entendi';
$string['response2'] = '😐 Mais ou menos';
$string['response2short'] = 'Mais ou menos';
$string['response3'] = '🙂 Entendi';
$string['response3short'] = 'Entendi';
$string['response4'] = '😄 Domino';
$string['response4short'] = 'Domino';
$string['responsecannotchange'] = 'Esta atividade não permite alterar a resposta depois do envio.';
$string['responselocked'] = 'Sua resposta já foi registrada e não pode ser alterada.';
$string['responses'] = 'respostas';
$string['responsesaved'] = 'Sua resposta foi registrada.';
$string['sendresponse'] = 'Enviar resposta';
