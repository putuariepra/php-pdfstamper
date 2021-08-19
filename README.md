# PHP Pdfstamper: Add an image to PDF's pages
This package using [crossref/pdfstamp](https://gitlab.com/crossref/pdfstamp)

| Function | Params | Description |
|--------|-----------|-------------|
| render() |  | To process adding an image to PDF's pages and get the output |
| setStampUrl(url) | url: string | Add url redirection to the image |
| setDpi(dpi) | dpi: numeric | Set image's DPI. Default DPI is 300  |
| setLocation(x,y) | x: numeric <br> y: numeric | Set coordinate location of image in page. Default (x,y): 0,0 |
| setPage(page) | page: numeric or array (e.g: [1,4,5]) | Set pages to add image. Multiple pages are allowed. Default: page 1 |
| setPageRange(range) | range: array [start,end] <br> (e.g: [[1,3], [5,7]]) | Set pages in range to add image. Multiple pages are allowed. Default: page 1 |
| overwrite() |  | Overwrite the stamped image if already exists. Default: false |
| disableValidation() |  | Disable validation of PDF file (exists and must be pdf), image (exists and must be image format) and output directory (writeable) |

Examples
------------
Standard usage

    use PdfStamper\PdfStamper;
    
    PdfStamper::stamp(
        '/dir/targetfile.pdf',
        '/dir/image.jpg'
    )    
    ->setLocation(40,40)
    ->overwrite()
    ->render();

Determine output directory

    use PdfStamper\PdfStamper;
    
    PdfStamper::stamp(
        '/dir/targetfile.pdf',
        '/dir/image.jpg',
        '/dir/output'
    )    
    ->setLocation(40,40)
    ->overwrite()
    ->render();

Set a page (e.g: page number 2)

    use PdfStamper\PdfStamper;
    
    PdfStamper::stamp(
        '/dir/targetfile.pdf',
        '/dir/image.jpg'
    )    
    ->setLocation(40,40)
    ->overwrite()
    ->setPage(2)
    ->render();
    
Set pages (e.g: pages 1, 3, and 4)

    use PdfStamper\PdfStamper;
    
    PdfStamper::stamp(
        '/dir/targetfile.pdf',
        '/dir/image.jpg'
    )    
    ->setLocation(40,40)
    ->overwrite()
    ->setPage([1,3,4])
    ->render();
    

Set pages using range (e.g: pages 1-3 and 6-8)

    use PdfStamper\PdfStamper;
    
    PdfStamper::stamp(
        '/dir/targetfile.pdf',
        '/dir/image.jpg'
    )    
    ->setLocation(40,40)
    ->overwrite()
    ->setPageRange([
      [1,3],
      [6,8]
    ])
    ->render();

Requirements
------------
- PHP: >=5.6.0
- [JRE](https://www.java.com/)

Installation
------------
> This package can be installed either in native PHP or Framework such Laravel

Run composer require to install

    composer require putuariepra/php-pdfstamper

Credit
------------
- [crossref](https://gitlab.com/crossref)
