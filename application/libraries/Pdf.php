<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter DomPDF Library
 *
 * Generate PDF's from HTML in CodeIgniter
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Ardianta
 * @license         MIT License
 * @link            https://github.com/dompdf/dompdf
 */

require_once APPPATH . 'third_party/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

class Pdf extends Dompdf
{
    /**
     * PDF filename
     * @var string
     */
    public $filename;

    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->filename = "laporan.pdf";
    }

    /**
     * Get an instance of CodeIgniter
     *
     * @access  protected
     * @return  void
     */
    protected function ci()
    {
        return get_instance();
    }

    /**
     * Load a CodeIgniter view to HTML
     *
     * @access  public
     * @param   string  $view
     * @param   array   $data
     * @return  void
     */
    public function load_view($view, $data = array())
    {
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->loadHtml($html);
    }

    /**
     * Stream the PDF to the browser
     *
     * @access  public
     * @param   string  $filename
     * @param   array   $options
     * @return  void
     */
    public function stream_pdf($filename = null, $options = array("Attachment" => false))
    {
        if ($filename) {
            $this->filename = $filename;
        }
        $this->render();
        $this->stream($this->filename, $options);
    }
}
