<?php
if (class_exists('FPDF', false)) {
    die("DUPLICATE FPDF LOAD DETECTED in: " . __FILE__);
}
/*******************************************************************************
* FPDF (v1.85)
* http://www.fpdf.org/
* Copyright (c) 2009 Olivier PLATHEY
* Licensed under Creative Commons Attribution 2.0
*******************************************************************************/

class FPDF {
protected $page;               // page number
protected $n;                  // current object number
protected $offsets;            // array of object offsets
protected $buffer;             // buffer holding in-memory PDF
protected $pages;              // array containing footprint of each page
protected $state;              // current document state
protected $compress;           // compression flag
protected $k;                  // scale factor (number of points in user unit)
protected $DefOrientation;     // default orientation
protected $CurOrientation;     // current orientation
protected $StdPageSizes;       // standard page sizes
protected $DefPageSize;        // default page size
protected $CurPageSize;        // current page size
protected $CurRotation;        // current page rotation
protected $PageInfo;           // page-related data
protected $wPt, $hPt;          // dimensions of current page in points
protected $w, $h;              // dimensions of current page in user unit
protected $lMargin;            // left margin
protected $tMargin;            // top margin
protected $rMargin;            // right margin
protected $bMargin;            // page break margin
protected $cMargin;            // cell margin
protected $x, $y;              // current position in user unit
protected $lasth;              // height of last printed cell
protected $LineWidth;          // line width in user unit
protected $fontpath;           // path containing fonts
protected $CoreFonts;          // array of core font names
protected $fonts;              // array of used fonts
protected $FontFiles;          // array of font files
protected $encodings;          // array of encodings
protected $cmaps;              // array of ToUnicode CMaps
protected $FontFamily;         // current font family
protected $FontStyle;          // current font style
protected $underline;          // underlining flag
protected $CurrentFont;        // current font info
protected $FontSizePt;         // current font size in points
protected $FontSize;           // current font size in user unit
protected $DrawColor;          // commands for drawing color
protected $FillColor;          // commands for filling color
protected $TextColor;          // commands for text color
protected $ColorFlag;          // indicates whether fill and text colors are different
protected $WithAlpha;          // indicates whether alpha channel is used
protected $ws;                 // word spacing
protected $images;             // array of used images
protected $PageLinks;          // array of links in pages
protected $links;              // array of internal links
protected $AutoPageBreak;      // automatic page breaking
protected $PageBreakTrigger;   // threshold used to trigger page breaks
protected $InHeader;           // flag set when processing header
protected $InFooter;           // flag set when processing footer
protected $AliasNbPages;       // alias for total number of pages
protected $ZoomMode;           // zoom display mode
protected $LayoutMode;         // layout display mode
protected $metadata;           // document properties
protected $PDFVersion;         // PDF version number

// Private properties
var $AutoIndentContent = false; // whether to indent content automatically
var $AutoIndentMargins = array(-1, -1, -1, -1); // indent margins (l, t, r, b)
var $fwPt, $fhPt; // page width/height in points
var $fw, $fh; // page width/height in user unit
var $mrl, $mrr; // margin left/right in user unit
var $mt, $mb; // margin top/bottom in user unit
var $rm; // right margin in user unit
var $tbl, $tbr; // table left/right position
var $td, $tw; // table depth/width
var $talign; // table alignment
var $tindent; // table indent
var $thead; // table header flag
var $tfoot; // table footer flag
var $tstart; // table start Y
var $trows; // table rows
var $tcells; // table cells
var $tcell; // current cell
var $trow; // current row
var $tcol; // current column

function __construct($orientation='P', $unit='mm', $size='A4') {
    // Initialization
    $this->_dochecks();
    $this->state = 0;
    $this->page = 0;
    $this->n = 2;
    $this->buffer = '';
    $this->pages = array();
    $this->PageInfo = array();
    $this->fonts = array();
    $this->FontFiles = array();
    $this->encodings = array();
    $this->cmaps = array();
    $this->images = array();
    $this->links = array();
    $this->InHeader = false;
    $this->InFooter = false;
    $this->AliasNbPages = '';
    $this->ZoomMode = 'fullpage';
    $this->LayoutMode = 'single';
    $this->metadata = array();
    $this->PDFVersion = '1.3';
    $this->DefOrientation = $orientation;
    $this->DefPageSize = $size;
    $this->SetMargins(27, 17, 17);
    $this->SetAutoPageBreak(true, 25);
    $this->SetDisplayMode('default');
    $this->fontpath = '';
    $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
    $this->SetFont('Arial');
    $this->compress = false;
    $this->k = $this->_getScaleFactor($unit);
    $this->SetCreator('FPDF');
    $this->SetTitle('');
    $this->SetSubject('');
    $this->SetAuthor('');
    $this->SetKeywords('');
    $this->SetProducer('');
    $this->_initPageSize($size);
    $this->_initOrientation($orientation);
}

function SetMargins($left, $top, $right=null) {
    $this->lMargin = $left;
    $this->tMargin = $top;
    if($right===null)
        $right = $left;
    $this->rMargin = $right;
}

function SetLeftMargin($margin) {
    $this->lMargin = $margin;
    if($this->page>0 && $this->x<$margin)
        $this->x = $margin;
}

function SetTopMargin($margin) {
    $this->tMargin = $margin;
}

function SetRightMargin($margin) {
    $this->rMargin = $margin;
}

function SetAutoPageBreak($auto, $margin = 0) {
    $this->AutoPageBreak = $auto;
    $this->bMargin = $margin;
    $this->PageBreakTrigger = $this->h - $margin;
}

function AddPage($orientation = '', $size = '', $rotation = 0) {
    if($this->state==3)
        $this->Error('The document is closed');
    $family = $this->FontFamily;
    $style = $this->FontStyle.($this->underline ? 'U' : '');
    $fontsize = $this->FontSizePt;
    $lw = $this->LineWidth;
    $dc = $this->DrawColor;
    $fc = $this->FillColor;
    $tc = $this->TextColor;
    $cf = $this->ColorFlag;
    if($this->page>0) {
        $this->_endpage();
    }
    $this->page++;
    $this->PageInfo[$this->page] = array('orientation' => $orientation, 'size' => $size, 'rotation' => $rotation);
    $this->x = $this->lMargin;
    $this->y = $this->tMargin;
    $this->FontFamily = $family;
    $this->FontStyle = $style;
    $this->FontSizePt = $fontsize;
    $this->FontSize = $fontsize/$this->k;
    $this->LineWidth = $lw;
    $this->DrawColor = $dc;
    $this->FillColor = $fc;
    $this->TextColor = $tc;
    $this->ColorFlag = $cf;
    $this->AutoIndentContent = false;
    $this->AutoIndentMargins = array(-1, -1, -1, -1);
    if($orientation!='')
        $this->_setPageOrientation($orientation);
    if($size!='')
        $this->_setPageSize($size);
    $this->_setPageRotation($rotation);
    if($this->page==1)
        $this->_putheader();
    $this->_putpages();
}

function SetDisplayMode($zoom, $layout='default') {
    $this->ZoomMode = $zoom;
    $this->LayoutMode = $layout;
}

function SetCreator($creator) {
    $this->metadata['Creator'] = $creator;
}

function SetAuthor($author) {
    $this->metadata['Author'] = $author;
}

function SetTitle($title) {
    $this->metadata['Title'] = $title;
}

function SetSubject($subject) {
    $this->metadata['Subject'] = $subject;
}

function SetKeywords($keywords) {
    $this->metadata['Keywords'] = $keywords;
}

function SetProducer($producer) {
    $this->metadata['Producer'] = $producer;
}

function SetFont($family, $style='', $size=0) {
    $family = strtolower($family);
    if($family=='')
        $family = $this->FontFamily;
    if($family=='arial')
        $family = 'helvetica';
    elseif($family=='symbol' || $family=='zapfdingbats')
        $style = '';
    $style = strtoupper($style);
    if(strpos($style,'U')!==false) {
        $this->underline = true;
        $style = str_replace('U','',$style);
    }
    else
        $this->underline = false;
    if($style=='IB')
        $style = 'BI';
    if($size==0)
        $size = $this->FontSizePt;
    $search = $family.'-'.$style;
    if(isset($this->fonts[$search])) {
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont =& $this->fonts[$search];
        return;
    }
    if(!isset($this->CoreFonts[$family]))
        $this->Error('Undefined font family: '.$family);
    $this->AddFont($family, $style, '', true);
    $this->SetFont($family, $style, $size);
}

function AddFont($family, $style='', $file='', $core=false) {
    if($core) {
        $fontkey = strtolower($family).'-'.$style;
        if(isset($this->fonts[$fontkey]))
            return;
        $name = '';
        $cw = '';
        $enc = '';
        $file = '';
        if($family=='courier')
            $name = 'Courier';
        elseif($family=='symbol')
            $name = 'Symbol';
        elseif($family=='times' || $family=='helvetica')
            $name = ucfirst($family);
        if($name=='')
            $this->Error('Undefined core font family: '.$family);
        $stylekey = strtoupper($style);
        if($stylekey=='')
            $stylekey = 'Regular';
        elseif($stylekey=='B')
            $stylekey = 'Bold';
        elseif($stylekey=='I')
            $stylekey = 'Italic';
        elseif($stylekey=='BI' || $stylekey=='IB')
            $stylekey = 'BoldItalic';
        $this->fonts[$fontkey] = array(
            'n' => 0,
            'type' => 'core',
            'name' => $name,
            'style' => $stylekey,
            'cw' => $cw
        );
    }
}

function SetFontSize($size) {
    $this->FontSizePt = $size;
    $this->FontSize = $size/$this->k;
}

function SetLineWidth($width) {
    $this->LineWidth = $width;
}

function Line($x1, $y1, $x2, $y2) {
    $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Rect($x, $y, $w, $h, $style='') {
    if($style=='F')
        $op = 'f';
    elseif($style=='FD' || $style=='DF')
        $op = 'B';
    else
        $op = 'S';
    $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function AddPage() {
    $this->_addpage();
}

function _addpage() {
    $this->page++;
    $this->x = $this->lMargin;
    $this->y = $this->tMargin;
}

function SetXY($x, $y) {
    $this->x = $x;
    $this->y = $y;
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='') {
    $k = $this->k;
    if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
        $x = $this->x;
        $this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
        $this->x = $x;
    }
    if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
    $s = '';
    if($fill || $border==1) {
        if($fill)
            $op = ($border==1) ? 'B' : 'f';
        else
            $op = 'S';
        $s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    }
    if(is_string($border)) {
        $x = $this->x;
        $y = $this->y;
        if(strpos($border,'L')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'T')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        if(strpos($border,'R')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        if(strpos($border,'B')!==false)
            $s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    }
    if($txt!='') {
        if(!isset($this->fonts[$this->FontFamily.'-'.$this->FontStyle]))
            $this->Error('Undefined font: '.$this->FontFamily.' '.$this->FontStyle);
        if($align=='R')
            $dx = $w-$this->cMargin-$this->GetStringWidth($txt);
        elseif($align=='C')
            $dx = ($w-$this->GetStringWidth($txt))/2;
        else
            $dx = $this->cMargin;
        if($this->ColorFlag)
            $s .= 'q '.$this->TextColor.' ';
        $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
        if($this->underline)
            $s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
        if($this->ColorFlag)
            $s .= ' Q';
        if($link)
            $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
    }
    if($s)
        $this->_out($s);
    $this->lasth = $h;
    if($ln>0) {
        $this->y += $h;
        if($ln==1)
            $this->x = $this->lMargin;
    }
    else
        $this->x += $w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0) {
    $cw =& $this->CurrentFont['cw'];
    if($w==0)
        $w = $this->w-$this->rMargin-$this->x;
    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    $s = str_replace("\r",'',$txt);
    $nb = strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
        $nb--;
    $b = 0;
    $i = 0;
    $j = 0;
    $sep = -1;
    $j2 = 0;
    $ascii = true;
    $len = strlen($s);
    $nl = true;
    $cwidth = '';
    $cpos = 0;
    $ns = 0;
    $b2 = 0;
    while($b<$len) {
        $c = $s[$b];
        if($c=="\n") {
            $i++;
            $sep = -1;
            $j = $j2;
            $cpos = $b+1;
            $ns = 0;
            $b2 = 0;
            $nl = true;
        }
        else if($c==' ') {
            $sep = $b;
            $j2 = $ns;
        }
        $ascii = (ord($c)<127);
        $width = $cw[$c];
        if($width==undefined)
            $width = 600;
        $j += $width;
        $ns += $width;
        if($j>$wmax) {
            if($sep==-1) {
                if($i==0) {
                    if($this->x> $this->lMargin)
                        $this->x = $this->lMargin;
                    $this->Cell($w,$h,substr($s, $b, $j2),$border,2,$align,$fill);
                }
                else {
                    $this->Cell(($wmax-$b2)/1000*$this->FontSize,$h,substr($s,$cpos,$j2),$border,2,$align,$fill);
                }
                $i++;
                $sep = $b;
                $j2 = $ns;
                $cpos = $b;
                $ns = $width;
                $b2 = $width;
            } else {
                if($nl==true)
                    $this->Cell(($wmax-$b2)/1000*$this->FontSize,$h,substr($s,$cpos,$j2),$border,2,$align,$fill);
                $i++;
                $sep = $b;
                $j2 = $ns;
                $cpos = $b;
                $ns = $width;
                $b2 = $width;
                $nl = false;
            }
        }
        $b++;
    }
    if($j>$wmax || $b==$len) {
        if($i==0)
            $this->Cell($w,$h,$txt,$border,2,$align,$fill);
        else {
            $this->Cell(($wmax-$b2)/1000*$this->FontSize,$h,substr($s,$cpos,$j2),$border,2,$align,$fill);
        }
    }
}

function Ln($h=null) {
    if($h===null)
        $h = $this->lasth;
    $this->y += $h;
}

function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='') {
    if(!isset($this->images[$file])) {
        $info = $this->_parseimg($file);
        if($info==false)
            $this->Error('Missing or incorrect image file: '.$file);
        $this->images[$file] = $info;
    }
    $info =& $this->images[$file];
    if($x===null)
        $x = $this->x;
    if($y===null)
        $y = $this->y;
    if($w==0 && $h==0) {
        $w = $info['w']/$this->k;
        $h = $info['h']/$this->k;
    }
    if($w==0)
        $w = $h*$info['w']/$info['h'];
    if($h==0)
        $h = $w*$info['h']/$info['w'];
    $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,-$h*$this->k,$x*$this->k,($this->h-$y-$h)*$this->k,$info['i']));
    if($link)
        $this->Link($x,$y,$w,$h,$link);
}

function GetX() {
    return $this->x;
}

function SetX($x) {
    if($x>=0)
        $this->x = $x;
    else
        $this->x = $this->w+$x;
}

function GetY() {
    return $this->y;
}

function SetY($y) {
    if($y>=0)
        $this->y = $y;
    else
        $this->y = $this->h+$y;
}

function SetXY($x, $y) {
    $this->SetX($x);
    $this->SetY($y);
}

function Output($dest='', $name='', $isUTF8=false) {
    if($this->state<3)
        $this->Close();
    if($dest=='')
        $dest = 'I';
    if($dest=='I')
        $destData = $this->_getData();
    else {
        $destData = $this->_getData();
        $this->_writeFile($destData, $dest, $name, $isUTF8);
        return '';
    }
    return $destData;
}

function Close() {
    if($this->state==3)
        return;
    if($this->page==0)
        $this->AddPage();
    $this->_endpage();
    $this->_enddoc();
}

function _getData() {
    $this->_putheader();
    $this->_putpages();
    $this->_putresources();
    $this->_putinfo();
    $this->_putcatalog();
    return $this->buffer;
}

function _putheader() {
    $this->_out('%PDF-'.$this->PDFVersion);
}

function _putpages() {
    $nb = $this->page;
    if(!empty($this->AliasNbPages)) {
        $alias = '/'.$this->AliasNbPages;
        $r = $this->n+2;
        $nbpages = '('.$nb.')';
        for($i=1;$i<=$nb;$i++)
            $pages[$r+$i-1] = '/'.$i.' '.$nbpages.' '.$alias.' '.$pages[$r+$i-1];
    }
    $this->_out('<<');
    $this->_out('/Type /Pages');
    $this->_out('/Kids ['.$kids.']');
    $this->_out('/Count '.$nb.'>>');
    $this->_out('endobj');
}

function _putresources() {
    $this->_out('<<');
    $this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
    $this->_out('/Font <<');
    foreach($this->fonts as $font) {
        $this->_out('/F'.$font['n'].' '.$font['n'].' 0 R');
    }
    $this->_out('>>');
    $this->_out('/XObject <<');
    foreach($this->images as $image)
        $this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
    $this->_out('>>');
    $this->_out('>>');
    $this->_out('endobj');
}

function _putinfo() {
    $this->_out('/Producer '.($this->n).' 0 R');
    $this->_out('>>');
    $this->_out('endobj');
    $this->_out('/Info '.($this->n).' 0 R');
}

function _putcatalog() {
    $this->_out('/Type /Catalog');
    $this->_out('/Pages 1 0 R');
    if($this->ZoomMode=='fullpage')
        $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
    elseif($this->ZoomMode=='fullwidth')
        $this->_out('/OpenAction [3 0 R /XYZ 0 null null]');
    elseif($this->ZoomMode=='real')
        $this->_out('/OpenAction [3 0 R /XYZ null null null]');
    elseif(!is_string($this->ZoomMode))
        $this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
    else
        $this->_out('/OpenAction [3 0 R /XYZ null null 0]');
    $this->_out('>>');
    $this->_out('endobj');
}

function _putresources() {
    $this->_out('<<');
    $this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
    $this->_out('/Font <<');
    foreach($this->fonts as $font) {
        $fontkey = $font['n'];
        $this->_out('/F'.$fontkey.' '.$fontkey.' 0 R');
    }
    $this->_out('>>');
    $this->_out('/XObject <<');
    foreach($this->images as $image) {
        $this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
    }
    $this->_out('>>');
    $this->_out('>>');
    $this->_out('endobj');
}

function _newobj() {
    $this->n++;
    $this->offsets[$this->n] = strlen($this->buffer);
    $this->_out($this->n.' 0 obj');
}

function _putstream($s) {
    $this->_out('stream');
    $this->_out($s);
    $this->_out('endstream');
    $this->_out('endobj');
}

function _out($s) {
    if($this->state==2)
        $this->pages[$this->page] .= $s."\n";
    else
        $this->buffer .= $s."\n";
}

function _dochecks() {
    $required = array('Fpdf' => class_exists('FPDF'));
    foreach($required as $name => $condition) {
        if(!$condition)
            $this->Error('Unsupported feature: '.$name);
    }
}

function Error($msg) {
    throw new Exception('FPDF error: '.$msg);
}

// End of FPDF class
?>

