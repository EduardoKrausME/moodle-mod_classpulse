# mod_pulse - Pulso da turma

Atividade Moodle para coletar um sinal rápido de compreensão durante a aula.

Opções padrão:

- 😕 Não entendi
- 😐 Mais ou menos
- 🙂 Entendi
- 😄 Domino

O professor pode configurar a pergunta, permitir ou bloquear alteração da resposta, ativar modo anônimo para o professor e escolher o gráfico padrão do relatório. O relatório agregado atualiza automaticamente enquanto está aberto e permite alternar entre pizza, barras e linha.

## Privacidade do modo anônimo

Quando o modo anônimo está ativo, `pulse_votes.userid` é gravado como `0`. Para impedir múltiplas respostas do mesmo usuário, é salvo um HMAC SHA-256 específico daquela atividade em `respondenthash`. O painel do professor nunca recebe esse identificador e trabalha somente com contagens agregadas.

Esse mecanismo oferece anonimato na interface e no relatório docente, mas deve ser entendido como pseudonimização perante administradores com acesso privilegiado ao banco de dados e ao código do site.

## Compatibilidade

- Moodle 4.5 ou superior.
- PHP compatível com a versão do Moodle utilizada.

### Backup e restauração

A configuração da atividade é incluída normalmente no backup. Respostas identificadas são restauradas quando os respectivos usuários são restaurados. Respostas de atividades anônimas não são levadas no backup de dados de usuários, evitando criar respostas órfãs ou duplicadas quando IDs de usuários mudam entre sites.
