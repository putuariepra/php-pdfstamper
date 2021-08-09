<?php
namespace PdfStamper;

class PdfStamper {
  private $dirLib;
  private $fileName;
  private $fileExt;
  private $fileDir;

  /**
   * 0: Default
   * 1: Current Working Dir Mode (CWD). Copy file to current working dir and use the file to stamping
   */
  private $mode = 0;
  private $validate = 1;

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

  protected $supportedImageFormat = [
    'png' => 'image/png',
    'jpe' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp',
    'ico' => 'image/vnd.microsoft.icon',
    'tiff' => 'image/tiff',
    'tif' => 'image/tiff',
    'svg' => 'image/svg+xml',
    'svgz' => 'image/svg+xml',
  ];

  function __construct($targetPdf, $imagePath, $outpurDir=null) {
    $this->dirLib = \dirname(__FILE__);

    $fileinfo = \pathinfo($targetPdf);    
    $this->fileName = $fileinfo['filename'];
    $this->fileExt = $fileinfo['extension'];
    $this->fileDir = $fileinfo['dirname'];

    $this->targetPdf = $targetPdf;
    $this->imagePath = $imagePath;
    $this->outpurDir = $outpurDir;    
  }  

  public function render() {
    $imagePath = $this->imagePath;
    $targetPdf = $this->targetPdf;
    $outpurDir = $this->outpurDir;

    if (is_null($outpurDir)) {
      $outpurDir = $this->fileDir;
    }

    if ($this->validate) {      
      if (! \is_readable($this->targetPdf)) {
        return $this->getOutput(false, 'PDF is not found nor readable');
      }
      if (! \is_readable($this->imagePath)) {
        return $this->getOutput(false, 'Image is not found nor readable');
      }
      if (! \is_writable($outpurDir)) {
        return $this->getOutput(false, "Output folder {$outpurDir} is not found nor writeable");
      }
      if (\mime_content_type($this->targetPdf) != 'application/pdf') {
        return $this->getOutput(false, 'Supported file format is only PDF');
      }
      if (! \in_array(\mime_content_type($this->imagePath), $this->supportedImageFormat)) {
        return $this->getOutput(false, 'Image file format is not supported');
      }
    }

    $outputPath = $this->getOutputPath($outpurDir);
    if (\file_exists($outputPath) && !$this->overwrite) {                  
      return $this->getOutput(false, 'Stamped file already exists');      
    }

    $dpi = '';
    if (!is_null($this->dpi)) {
      $dpi = ' -d '.$dpi;
    }

    $stampUrl = '';
    if (!is_null($this->stampUrl)) {
      $stampUrl = ' -u '.$this->stampUrl;
    }

    $outputCmd = '';
    if (!is_null($this->outpurDir)) {
      $outputCmd = ' -o '.$this->outpurDir;
    }    

    $pageCmd = '';
    if (count($this->pageSingle) > 0) {
      foreach ($pageCmd as $value) {
        if (!empty($value)) {
          $pageCmd .= ' -p '.$value;
        }
      }
    }
    if (count($this->pageRange) > 0) {
      foreach ($pageCmd as $value) {
        if (!empty($value[0]) && !empty($value[1])) {
          if ($value[0] > $value[1]) {            
            $pageCmd .= ' -pp '.$value[1].'-'.$value[0];
          }else {
            $pageCmd .= ' -pp '.$value[0].'-'.$value[1];
          }
        }
      }
    }

    /**
     * Copy file to current working dir and use the file to stamping
     */
    if ($this->mode == 1) {
      $cwd = \getcwd();
      $new_file = $cwd.'/'.$this->fileName.".".$this->fileExt;
      $image_filename = \basename($this->imagePath);
      $new_image = $cwd.'/'.$image_filename;
      if (!copy($this->targetPdf,$new_file)) {
        return $this->getOutput(false, "Failed to copy file {$this->fileName} to {$cwd}");
      }      
      if (!copy($this->imagePath,$new_image)) {
        return $this->getOutput(false, "Failed to copy file {$this->fileName} to {$cwd}");
      }

      $imagePath = $image_filename;
      $targetPdf = $this->fileName.".".$this->fileExt;
    }

    $command = "java -jar {$this->dirLib}/pdfstamp.jar{$dpi}{$stampUrl}{$outputCmd}{$pageCmd} -i {$imagePath} -l {$this->locX},{$this->locY} {$targetPdf} 2>&1";

    try {    
      \exec($command, $val, $err);
    } catch (\Exception $e) {      
      return $this->getOutput(false, $e->getMessage());
    }

    if ($this->mode == 1) {
      \unlink($new_file);
      \unlink($new_image);
    }

    if (!empty($val)) {      
      return $this->getOutput(false, \implode(" ", $val));
    }

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

  public function setPage($pages = []) {
    $this->pageSingle = $pages;
  }

  public function setPageRange($ranges = []) {
    $this->pageRange = $ranges;
  }

  public function overwrite() {
    $this->overwrite = 1;
    return $this;
  }

  public function disableValidation() {
    $this->validate = 0;
    return $this;
  }

  public function modeCwd() {
    $this->mode = 1;
    return $this;
  }

  static public function stamp($targetPdf, $imagePath, $outpurDir=null) {
    return new self($targetPdf, $imagePath, $outpurDir);
  }

  protected function getOutputPath($outpurDir) {
    if (substr($outpurDir, -1, 1) != '/') {
      return "{$outpurDir}/{$this->fileName}_stamped.{$this->fileExt}";
    }

    return "{$outpurDir}{$this->fileName}_stamped.{$this->fileExt}";
  }

  protected function getOutput($success, $msg, $outputPath=null) {
    $output = new \stdClass;
    $output->success = $success;
    $output->message = $msg;    
    $output->output = $outputPath;
    return $output;
  }
}