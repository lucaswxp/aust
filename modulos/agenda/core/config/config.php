<?php
/**
 * Configurações
 *
 * Arquivo contendo informações sobre este módulo
 *
 * @package Modulos
 * @name Config
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.2
 * @since v0.1.5, 30/05/2009
 */
/**
 * Variável contendo as configurações deste módulo
 *
 * @global array $GLOBALS['modInfo']
 * @name $modInfo
 */

$modInfo = array(
    /**
     * Cabeçalho
     *
     * Informações sobre o próprio módulo, como nome, descriçao, entre outros
     */
    /**
     * É arquitetura MVC
     */
    'mvc' => true,
    /**
     * 'nome': Nome humano do módulo
     */
    'nome' => 'Agenda',
    /**
     * 'className': Classe oficial do módulo
     */
    'className' => 'Agenda',
    /**
     * 'descricao': Descrição que facilita compreender a função do módulo
     */
    'descricao' => 'Módulo para cadastro de compromissos.',
    /**
     * 'estrutura': Se pode ser instalada como estrutura (Textos podem)
     */
    'estrutura' => true,
    /**
     * 'somenteestrutura': É uma estrutura somente, sem categorias? (cadastros,
     * por exemplo)
     */
    'somenteestrutura' => false,
    /**
     * 'embed': É do tipo embed?
     */
    'embed' => false,
    /**
     * 'embedownform': É do tipo embed que tem seu próprio formulário?
     */
    'embedownform' => false,



    /**
     * RESPONSER
     */
    /**
     * Se possui método de leitura de resumo
     */
    'responser' => array(
        'actived' => true,
    ),

    /**
     * Opções de gerenciamento de conteúdo
     *
     * A opções a seguir dizem respeito a qualquer ação que envolva
     * a interação do módulo com conteúdo.
     */
    /**
     * Opções de gerenciamento deste módulo
     *
     */
    'opcoes' => array(
        'create' => 'Novo',
        'listing' => 'Ver agenda',
    ),

	'configurations' => array(
    	'ordenate' => array(
	        "value" => "",
	        "label" => "Ordenado",
	        "inputType" => "checkbox",
	    ),
	    /*
	     * Resumo
	     */
	    'resumo' => array(
	        "value" => "",
	        "label" => "Tem resumo?",
	        "inputType" => "checkbox",
	    ),
	    /*
	     * Resumo
	     */
	    'nova_categoria' => array(
	        "value" => "",
	        "label" => "Permite criar categoria?",
	        "inputType" => "checkbox",
	    ),
	    /*
	     * Resumo
	     */
	    'modo_de_visualizacao' => array(
	        "value" => "",
	        "label" => "Opção Modo de Visualização?",
	        "inputType" => "checkbox",
            'help' => ''
	    ),		
	),

    /**
     * RESPONSER
     *
     * A seguir, as configurações do módulo para que este possa apresentar um
     * resumo a qualquer requisitante (chamado responser).
     */
    'arquitetura' => array(
        'table' => 'textos',
        'foreignKey' => 'categoria',
    ),


    /**
     * '': 
     */
    '' => '',

    /**
     * CABEÇALHOS DE LISTAGEM
     */
    'contentHeader' => array(
        'campos' => array(
            'created_on','title','node'
        ),
        'camposNome' => array(
            'Data','Título','Categoria'
        ),
    )
);
?>