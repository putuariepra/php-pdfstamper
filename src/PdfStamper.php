<?php
namespace PdfStamper;

class PdfStamper {
  private $dirLib;
  private $fileName;
  private $fileExt;

  protected $targetPdf = null;
  protected $outpurDir = null;
  protected $overwrite = 0;

  protected $imagePath = null;
  protected $stampUrl = null;
  protected $locX = 0;
  protected $locY = 0;
  protected $dpi = null;

  protected $pageSingle = [];
  protected $pageRange = [];
  protected $pageExcepts = [];

  function __construct($targetPdf, $imagePath, $outpurDir) {
    $this->dirLib = \dirname(__FILE__);

    $fileinfo = \pathinfo($targetPdf);    
    $this->fileName = $fileinfo['filename'];
    $this->fileExt = $fileinfo['extension'];

    $this->targetPdf = $targetPdf;
    $this->imagePath = $imagePath;
    $this->outpurDir = $outpurDir;    
  }  

  public function render() {
    $dpi = '';
    if (!is_null($this->dpi)) {
      $dpi = ' -d '.$dpi;
    }

    $stampUrl = '';
    if (!is_null($this->stampUrl)) {
      $stampUrl = ' -u '.$this->stampUrl;
    }

    $outputPath = $this->getOutputPath();
    if (\file_exists($outputPath)) {            
      if ($this->overwrite) {
        \unlink($outputPath);
      }else{
        return $this->getOutput(false, 'Stamped file already exists');
      }
    }

    $command = "java -jar {$this->dirLib}/pdfstamp.jar{$dpi}{$stampUrl} -i '{$this->imagePath}' -l {$this->locX},{$this->locY} '{$this->targetPdf}' 2>&1";    
    \exec($command, $val, $err);    

    return $this->getOutput(true, 'Success', $outputPath);
  }  

  public function setStampUrl($url) {
    $this->stampUrl = $url;
    return $this;
  }
  public function setDpi($dpi) {
    $this->dpi = $dpi;
    return $this;
  }
  public function setLocation($x,$y) {
    $this->locX = $x;
    $this->locY = $y;
    return $this;
  }

  public function overwrite() {
    $this->overwrite = 1;
    return $this;
  }

  static public function stamp($targetPdf, $imagePath, $outpurDir) {
    return new self($targetPdf, $imagePath, $outpurDir);
  }

  protected function getOutputPath() {    
    if (substr($this->outpurDir, -1, 1) != '/') {
      return "{$this->outpurDir}/{$this->fileName}_stamped.{$this->fileExt}";
    }

    return "{$this->outpurDir}{$this->fileName}_stamped.{$this->fileExt}";
  }

  protected function getOutput($success, $msg, $outputPath=null) {
    $output = new \stdClass;
    $output->success = $success;
    $output->message = $msg;    
    $output->output = $outputPath;
    return $output;
  }
}