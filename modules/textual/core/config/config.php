<?php
/**
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.5, 30/05/2009
 */
$modInfo = array(

    'name' => 'Textual',
    /**
     * 'className': the model's class name
     */
    'className' => 'Textual',
    'description' => 'Manage simple texts.',


    'actions' => array(
        'create' => 'Novo',
        'listing' => 'Listar',
    ),

    'configurations' => array(
        /*
         * Has file
         */
        'has_file' => array(
            "value" => "",
            "label" => "Tem arquivo?",
            "inputType" => "checkbox",
        ),
        /*
         * Ordenate
         */
        'ordenate' => array(
            "value" => "",
            "label" => "Ordenado",
            "inputType" => "checkbox",
        ),
        /*
         * summary
         */
        'summary' => array(
            "value" => "",
            "label" => "Tem resumo?",
            "inputType" => "checkbox",
        ),
        'new_aust_node' => array(
            "value" => "",
            "label" => "Permite criar categoria?",
            "inputType" => "checkbox",
        ),
        'generate_preview_url' => array(
            "value" => "",
            "label" => "Mostrar Url do conteúdo?",
            "inputType" => "text",
            'help' => 'A seguir, os códigos especiais: <ul>'.
                      '<li>%id = id do conteúdo</li>'.
                      '<li>%title_encoded = título encoded</li>'.
                      '<li>%category = categoria</li>'.
                      '</ul>'.
                      'Exemplo: http://meusite.com.br/noticias/%id.'
        ),
        /*
         * Há opção para selecionar o modo de visualização?
         */
        'upload_inline_images' => array(
            "value" => "0",
            "label" => "Permitir upload de imagens no TinyMCE?",
            "inputType" => "checkbox",
        ),

	    'manual_date' => array(
	        "value" => "",
	        "label" => "Ajustar data manualmente?",
	        "inputType" => "checkbox",
	    ),

	    'show_visits_counter' => array(
	        "value" => "",
	        "label" => "Mostrar contador de visitas",
	        "inputType" => "checkbox",
	    ),
    ),
    
    /*
     * Se não há valor, substitui campo vazio na listagem
     * pelos valores abaixo
     */
    'replaceFieldsValueIfEmpty' => array(
        'title' => '[Sem título]',
    ),

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
