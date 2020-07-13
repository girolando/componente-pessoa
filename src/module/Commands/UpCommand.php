<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 04/08/2016
 * Time: 10:28
 */

namespace Girolando\Componentes\Pessoa\Commands;


use Illuminate\Console\Command;

class UpCommand extends Command
{
    protected $signature = 'girolando:componentepessoa:up';
    protected $description = 'Instala a view necessária para o componente funcionar. Nesse caso a view é comp.vPessoa';

    public function handle()
    {
        try {
            $this->info('Rodando migration da view');
            $this->migrate();
            $this->info('Migration Executada!');
        } catch (\Exception $e) {
            $this->error('Houve uma falha: '. $e->getMessage());
        }
    }


    private function migrate()
    {
        \DB::statement("
            create view comp.VPessoa as
            select
                p.codigopessoa as id,
                a.codigoAssociado as codCfoCriador,
                (case 
                    when a.tipoAssociado = 1 and a.statusAssociado = 1 then 'ASSOC'
                    when a.tipoAssociado is null and a.statusAssociado is null then null
                    else 'NASSOC' end) as idTipoCriador,
                a.statusAssociado as statusCriador,
                p.nomePessoa,
                p.cpfPessoa,
                p.emailPessoa,
                p.loginPessoa,
                p.senhaPessoa,
                p.ultimoAcessoPessoa,
                f.CODCFO,
                f.NOME,
                f.EMAIL,
                f.EMAILPGTO,
                F.NOMEFANTASIA,
                F.TELEFONE,
                F.TELEX,
                F.CGCCFO,
                F.RUA,
                F.CIDADE,
                F.CODETD,
                F.PAIS,
                F.CEP,
                F.BAIRRO,
                F.NUMERO,
                f.DATAULTALTERACAO,
                f.DTNASCIMENTO,
                vet.crmvVeterinario,
                (case when a.tipoAssociado = 1 then 1 else 0 end) as isAssociado,
                (case when a.tipoAssociado = 0 then 1 else 0 end) as isNaoAssociado,
                (case when a.statusAssociado = 1 then 1 else 0 end) as isCriadorAtivo,
                (case when a.codigoPessoa is not null then 1 else 0 end) as isCriador,
                coalesce(fun.statusFuncionario, 0) as isFuncionario,
                coalesce(tec.statusFuncionario, 0) as isTecnico,
                coalesce(vet.statusVeterinario, 0) as isVeterinario,
                (case when len(cgccfo) > 15 then 1 else 0 end) as isEmpresa,
                (case when rc.codigoPessoa is not null then 1 else 0 end) as isRebanhoColaborador,
                (case when rc.codigoPessoa is not null or a.statusAssociado = 1 then 1 else 0 end) as isCriadorAtivoOrRebColaborador,
                (case when cont.statusControlador = 1 then 1 else 0 end) as isControlador,
                f.COMPLEMENTO
            FROM 
                pessoa p
            left join Associado a on a.codigoPessoa = p.codigoPessoa
            left join Funcionario fun on fun.codigoPessoa = p.codigoPessoa and fun.bitTecnicoFuncionario = 0 and fun.matriculaFuncionario is not null and fun.tecnicoFuncionario is null
            left join Funcionario tec on tec.codigoPessoa = p.codigoPessoa and tec.bitTecnicoFuncionario = 1 and tec.codvenFuncionario is not null --cod. vendedor (apenas técnicos possuem)
            left join Veterinario vet on vet.codigoPessoa = p.codigoPessoa
            left join scl.Controlador cont on cont.codigoPessoa = p.codigoPessoa
            left join corpore.dbo.fcfo f on cast(f.codcfo as bigint) = p.codCfo
            left join (
                select * from tp.rebanhocolaborador rc
                join fazenda f on f.codigoFazenda = rc.id
                where statusRebanho = 'Inscrito'
                ) as rc on rc.codigoPessoa = p.codigoPessoa

        ");
    }
}