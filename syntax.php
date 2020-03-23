<?php
/**
 * DokuWiki Plugin schemadata (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Deniz Kilic <fz.deniz.kilic@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_schemadata extends DokuWiki_Syntax_Plugin
{
    protected $doi = null;
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 999;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        // $this->Lexer->addSpecialPattern('<FIXME>', $mode, 'plugin_schemadata');
       $this->Lexer->addEntryPattern('<doi>', $mode, 'plugin_schemadata');
    }

   public function postConnect()
   {
       $this->Lexer->addExitPattern('</doi>', 'plugin_schemadata');
   }

    /**
     * Handle matches of the schemadata syntax
     *
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     *
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER : 
              return false;
            case DOKU_LEXER_UNMATCHED :
              $this->doi = $match;
              return false;
            case DOKU_LEXER_EXIT :
              $data = array($state, $this->doi);
              $this->doi = null;
              return $data;
          }
          return array();
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string        $mode     Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array         $data     The data from the handler() function
     *
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        list($state, $doi) = $data;
        if ($mode != 'xhtml') return false;

        // modified version of https://gist.github.com/kjgarza/34ca6a866b4437bc1c07d84f208a01c4
        $html = '<script type="text/javascript">'."\n".'/*<![CDATA[*/';
        $html .= '(function($){
            $(document).ready(
              function() {
                var doi = ';
        $html .= "'$doi';"; //for example 10.5284/1015681
        $html .= 'if (doi === undefined) {return;}       
                var url = "https://data.crosscite.org/application/vnd.schemaorg.ld+json/"
                url += doi
          
                $.ajax({
                  url: url,
                  dataType: \'text\', // don\'t convert JSON to Javascript object
                  success: function(data) {
                    $(\'<script>\')
                      .attr(\'type\', \'application/ld+json\')
                      .text(data)
                      .appendTo(\'head\');
                  },
                  error: function (error) {
                    console.log(error.responseJSON);
                  }
                });
            });
          })(jQuery)';
        $html.= '/*!]]>*/'."\n".'</script>'."\n";
        $renderer->doc .= $html;
        return true;
    }
}

