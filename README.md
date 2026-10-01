# mod_classpulse - Pulso da turma

Atividade Moodle para coletar um sinal rápido de compreensão durante a aula.

As opções padrão são:

- 😕 Não entendi;
- 😐 Mais ou menos;
- 🙂 Entendi;
- 😄 Domino.

O professor pode configurar a pergunta, permitir ou bloquear alteração da resposta, ativar modo anônimo e escolher o
gráfico padrão do relatório. O relatório agregado atualiza automaticamente enquanto está aberto e permite alternar entre
pizza, barras e linha.

## Privacidade do modo anônimo

Quando o modo anônimo está ativo, `classpulse_votes.userid` é gravado como `0`. Para impedir múltiplas respostas do
mesmo usuário, é salvo um HMAC SHA-256 específico daquela atividade em `respondenthash`. O painel do professor nunca
recebe esse identificador e trabalha somente com contagens agregadas.

Esse mecanismo mantém o anonimato na interface e no relatório docente, embora administradores com acesso privilegiado
ao banco de dados e ao código possam tratar o identificador como pseudônimo técnico.

## Backup e restauração

A configuração da atividade é incluída no backup. Respostas identificadas são restauradas quando os respectivos usuários
também são restaurados. Em atividades anônimas, as respostas não são levadas no backup de dados de usuários, evitando
registros órfãos ou duplicados quando identificadores mudam entre ambientes.
