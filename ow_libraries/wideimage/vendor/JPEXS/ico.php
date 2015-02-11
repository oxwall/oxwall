<?php
/**
 * @package com.jpexs.image.bmp
 *
 * JPEXS ICO Image functions
 * @version 2.1
 * @author JPEXS
 * @copyright (c) JPEXS 2004-2009
 *
 * Webpage: http://www.jpexs.com
 * Email: jpexs@jpexs.com
 *
 * If you like my script, you can donate... visit my webpages or email me for more info.
 *
 *        Version changes:
 *                v2.1 - redesigned sourcecode, phpdoc included, all internal functions and global variables have prefix "jpexs_"
 *                v2.0 - For icons with Alpha channel now you can set background color
 *                     - ImageCreateFromExeIco added
 *                     - Fixed ICO_MAX_SIZE and ICO_MAX_COLOR values
 *
 * TODO list:
 *      - better error handling
 *      - class encapsulation
 * License:
 *      - you can freely use it
 *      - you can freely distribute sourcecode
 *      - you can freely modify it as long as you leave my copyright/author info in source code
 *      - if you developing closesource application, you should add my name at least to "about" page of your web application
 *      - if you create an amazing modification, please contact me... I can publish link to your webpage if you're interested...
 *      - if you want to use my script in commercial application for earning money, you should make a donation to me first
 */

/** TrueColor images constant  */
define("ICO_TRUE_COLOR", 0x1000000);
/** XPColor images constant (Alpha channel) */
define("ICO_XP_COLOR", 4294967296);
/** Image with maximum colors */
define("ICO_MAX_COLOR", -2);
/** Image with maximal size */
define("ICO_MAX_SIZE", -2);


/** TrueColor images constant
 * @deprecated Deprecated since version 2.1, please use ICO_ constants
 */
define("TRUE_COLOR", 0x1000000);
/** XPColor images constant (Alpha channel)
 * @deprecated Deprecated since version 2.1, please use ICO_ constants
 */
define("XP_COLOR", 4294967296);
/** Image with maximum colors
 * @deprecated Deprecated since version 2.1, please use ICO_ constants
 */
define("MAX_COLOR", -2);
/** Image with maximal size
 * @deprecated Deprecated since version 2.1, please use ICO_ constants
 */
define("MAX_SIZE", -2);


/**
 * Reads image from a ICO file
 *
 * @param string $filename Target ico file to load
 * @param int $icoColorCount Icon color count (For multiple icons ico file) - 2,16,256, ICO_TRUE_COLOR, ICO_XP_COLOR or ICO_MAX_COLOR
 * @param int $icoSize Icon width (For multiple icons ico file) or ICO_MAX_SIZE
 * @param int $alphaBgR Background color R value for alpha-channel images (Default is White)
 * @param int $alphaBgG Background color G value for alpha-channel images (Default is White)
 * @param int $alphaBgB Background color B value for alpha-channel images (Default is White)
 * @return resource Image resource
 */
function imageCreateFromIco($filename,$icoColorCount=16,$icoSize=16,$alphaBgR=255,$alphaBgG=255,$alphaBgB=255)
{
$Ikona=jpexs_GetIconsInfo($filename);

$IconID=-1;

$ColMax=-1;
$SizeMax=-1;

for($p=0;$p<count($Ikona);$p++)
{
$Ikona[$p]["NumberOfColors"]=pow(2,$Ikona[$p]["Info"]["BitsPerPixel"]);
};


for($p=0;$p<count($Ikona);$p++)
{

if(($ColMax==-1)or($Ikona[$p]["NumberOfColors"]>=$Ikona[$ColMax]["NumberOfColors"]))
if(($icoSize==$Ikona[$p]["Width"])or($icoSize==ICO_MAX_SIZE))
 {
  $ColMax=$p;
 };

if(($SizeMax==-1)or($Ikona[$p]["Width"]>=$Ikona[$SizeMax]["Width"]))
if(($icoColorCount==$Ikona[$p]["NumberOfColors"])or($icoColorCount==ICO_MAX_COLOR))
 {
   $SizeMax=$p;
 };


if($Ikona[$p]["NumberOfColors"]==$icoColorCount)
if($Ikona[$p]["Width"]==$icoSize)
 {

 $IconID=$p;
 };
};

  if($icoColorCount==ICO_MAX_COLOR) $IconID=$ColMax;
  if($icoSize==ICO_MAX_SIZE) $IconID=$SizeMax;

$ColName=$icoColorCount;

if($icoSize==ICO_MAX_SIZE) $icoSize="Max";
if($ColName==ICO_TRUE_COLOR) $ColName="True";
if($ColName==ICO_XP_COLOR) $ColName="XP";
if($ColName==ICO_MAX_COLOR) $ColName="Max";
if($IconID==-1) die("Icon with $ColName colors and $icoSize x $icoSize size doesn't exist in this file!");


jpexs_readIcon($filename,$IconID,$Ikona);

 $biBitCount=$Ikona[$IconID]["Info"]["BitsPerPixel"];


  if($Ikona[$IconID]["Info"]["BitsPerPixel"]==0)
  {
  $Ikona[$IconID]["Info"]["BitsPerPixel"]=24;
  };

 $biBitCount=$Ikona[$IconID]["Info"]["BitsPerPixel"];
 if($biBitCount==0) $biBitCount=1;


$Ikona[$IconID]["BitCount"]=$Ikona[$IconID]["Info"]["BitsPerPixel"];



if($Ikona[$IconID]["BitCount"]>=24)
{
$img=imagecreatetruecolor($Ikona[$IconID]["Width"],$Ikona[$IconID]["Height"]);
if($Ikona[$IconID]["BitCount"]==32):
  $backcolor=imagecolorallocate($img,$alphaBgR,$alphaBgG,$alphaBgB);
  imagefilledrectangle($img,0,0,$Ikona[$IconID]["Width"]-1,$Ikona[$IconID]["Height"]-1,$backcolor);
endif;
for($y=0;$y<$Ikona[$IconID]["Height"];$y++)
for($x=0;$x<$Ikona[$IconID]["Width"];$x++)
 {
 $R=$Ikona[$IconID]["Data"][$x][$y]["r"];
 $G=$Ikona[$IconID]["Data"][$x][$y]["g"];
 $B=$Ikona[$IconID]["Data"][$x][$y]["b"];
 if($Ikona[$IconID]["BitCount"]==32)
 {
 $Alpha=127-round($Ikona[$IconID]["Data"][$x][$y]["alpha"]*127/255);
 if($Ikona[$IconID]["Maska"][$x][$y]==1) $Alpha=127;
 $color=imagecolorexactalpha($img,$R,$G,$B,$Alpha);
 if($color==-1) $color=imagecolorallocatealpha($img,$R,$G,$B,$Alpha);
 }
 else
 {
 $color=imagecolorexact($img,$R,$G,$B);
 if($color==-1) $color=imagecolorallocate($img,$R,$G,$B);
 };

 imagesetpixel($img,$x,$y,$color);

 };

}
else
{
$img=imagecreate($Ikona[$IconID]["Width"],$Ikona[$IconID]["Height"]);
for($p=0;$p<count($Ikona[$IconID]["Paleta"]);$p++)
 $Paleta[$p]=imagecolorallocate($img,$Ikona[$IconID]["Paleta"][$p]["r"],$Ikona[$IconID]["Paleta"][$p]["g"],$Ikona[$IconID]["Paleta"][$p]["b"]);

for($y=0;$y<$Ikona[$IconID]["Height"];$y++)
for($x=0;$x<$Ikona[$IconID]["Width"];$x++)
 {
 imagesetpixel($img,$x,$y,$Paleta[$Ikona[$IconID]["Data"][$x][$y]]);
 };
};
$IsTransparent=false;  
for($y=0;$y<$Ikona[$IconID]["Height"];$y++)
for($x=0;$x<$Ikona[$IconID]["Width"];$x++)
 if($Ikona[$IconID]["Maska"][$x][$y]==1)
  {
   $IsTransparent=true;
   break;
  };
if($Ikona[$IconID]["BitCount"]==32)
{
 imagealphablending($img, false);
 if(function_exists("imagesavealpha"))
  imagesavealpha($img,true);
};

if($IsTransparent)
 {
  if(($Ikona[$IconID]["BitCount"]>=24)or(imagecolorstotal($img)>=256))
   {
   $img2=imagecreatetruecolor(imagesx($img),imagesy($img));
   imagecopy($img2,$img,0,0,0,0,imagesx($img),imagesy($img));
   imagedestroy($img);
   $img=$img2;
   imagetruecolortopalette($img,true,255);

   };
    $Pruhledna=imagecolorallocate($img,0,0,0);
    for($y=0;$y<$Ikona[$IconID]["Height"];$y++)
     for($x=0;$x<$Ikona[$IconID]["Width"];$x++)
      if($Ikona[$IconID]["Maska"][$x][$y]==1)
       {
        imagesetpixel($img,$x,$y,$Pruhledna);
       };
  imagecolortransparent($img,$Pruhledna);
 };

return $img;


};




function jpexs_readIcon($filename,$id,&$Ikona)
{
global $jpexs_currentBit;

$f=fopen($filename,"rb");

fseek($f,6+$id*16);
  $Width=jpexs_freadbyte($f);
  $Height=jpexs_freadbyte($f);
fseek($f,6+$id*16+12);
$OffSet=jpexs_freaddword($f);
fseek($f,$OffSet);

$p=$id;

  $Ikona[$p]["Info"]["HeaderSize"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["ImageWidth"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["ImageHeight"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["NumberOfImagePlanes"]=jpexs_freadword($f);
  $Ikona[$p]["Info"]["BitsPerPixel"]=jpexs_freadword($f);
  $Ikona[$p]["Info"]["CompressionMethod"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["SizeOfBitmap"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["HorzResolution"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["VertResolution"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["NumColorUsed"]=jpexs_freadlngint($f);
  $Ikona[$p]["Info"]["NumSignificantColors"]=jpexs_freadlngint($f);


 $biBitCount=$Ikona[$p]["Info"]["BitsPerPixel"];

 if($Ikona[$p]["Info"]["BitsPerPixel"]<=8)
  {

 $barev=pow(2,$biBitCount);

  for($b=0;$b<$barev;$b++)
    {
    $Ikona[$p]["Paleta"][$b]["b"]=jpexs_freadbyte($f);
    $Ikona[$p]["Paleta"][$b]["g"]=jpexs_freadbyte($f);
    $Ikona[$p]["Paleta"][$b]["r"]=jpexs_freadbyte($f);
    jpexs_freadbyte($f);
    };

$Zbytek=(4-ceil(($Width/(8/$biBitCount)))%4)%4;


for($y=$Height-1;$y>=0;$y--)
    {
     $jpexs_currentBit=0;
     for($x=0;$x<$Width;$x++)
      {
         $C=jpexs_freadbits($f,$biBitCount);
         $Ikona[$p]["Data"][$x][$y]=$C;
      };

    if($jpexs_currentBit!=0) {jpexs_freadbyte($f);};
    for($g=0;$g<$Zbytek;$g++)
     jpexs_freadbyte($f);
     };

}
elseif($biBitCount==24)
{
 $Zbytek=$Width%4;

   for($y=$Height-1;$y>=0;$y--)
    {
     for($x=0;$x<$Width;$x++)
      {
       $B=jpexs_freadbyte($f);
       $G=jpexs_freadbyte($f);
       $R=jpexs_freadbyte($f);
       $Ikona[$p]["Data"][$x][$y]["r"]=$R;
       $Ikona[$p]["Data"][$x][$y]["g"]=$G;
       $Ikona[$p]["Data"][$x][$y]["b"]=$B;
      }
    for($z=0;$z<$Zbytek;$z++)
     jpexs_freadbyte($f);
   };
}
elseif($biBitCount==32)
{
 $Zbytek=$Width%4;

   for($y=$Height-1;$y>=0;$y--)
    {
     for($x=0;$x<$Width;$x++)
      {
       $B=jpexs_freadbyte($f);
       $G=jpexs_freadbyte($f);
       $R=jpexs_freadbyte($f);
       $Alpha=jpexs_freadbyte($f);
       $Ikona[$p]["Data"][$x][$y]["r"]=$R;
       $Ikona[$p]["Data"][$x][$y]["g"]=$G;
       $Ikona[$p]["Data"][$x][$y]["b"]=$B;
       $Ikona[$p]["Data"][$x][$y]["alpha"]=$Alpha;
      }
    for($z=0;$z<$Zbytek;$z++)
     jpexs_freadbyte($f);
   };
};


//Maska
$Zbytek=(4-ceil(($Width/(8)))%4)%4;
for($y=$Height-1;$y>=0;$y--)
    {
     $jpexs_currentBit=0;
     for($x=0;$x<$Width;$x++)
      {
         $C=jpexs_freadbits($f,1);
         $Ikona[$p]["Maska"][$x][$y]=$C;
      };
    if($jpexs_currentBit!=0) {jpexs_freadbyte($f);};
    for($g=0;$g<$Zbytek;$g++)
     jpexs_freadbyte($f);
     };
//--------------

fclose($f);

};

function jpexs_GetIconsInfo($filename)
{
global $jpexs_currentBit;

$f=fopen($filename,"rb");

$Reserved=jpexs_freadword($f);
$Type=jpexs_freadword($f);
$Count=jpexs_freadword($f);
for($p=0;$p<$Count;$p++)
 {
  $Ikona[$p]["Width"]=jpexs_freadbyte($f);
  $Ikona[$p]["Height"]=jpexs_freadbyte($f);
  $Ikona[$p]["ColorCount"]=jpexs_freadword($f);
 if($Ikona[$p]["ColorCount"]==0) $Ikona[$p]["ColorCount"]=256;
  $Ikona[$p]["Planes"]=jpexs_freadword($f);
  $Ikona[$p]["BitCount"]=jpexs_freadword($f);
  $Ikona[$p]["BytesInRes"]=jpexs_freaddword($f);
  $Ikona[$p]["ImageOffset"]=jpexs_freaddword($f);
 };

if(!feof($f)):
  for($p=0;$p<$Count;$p++)
   {
    fseek($f,$Ikona[$p]["ImageOffset"]+14);
    $Ikona[$p]["Info"]["BitsPerPixel"]=jpexs_freadword($f);
   };
endif;
fclose($f);
return $Ikona;
};




/**
 * Reads image from a icon in exe file
 * @param string $filename Target exefile
 * @param int $icoIndex Index of the icon in exefile
 * @param int $icoColorCount Icon color count (For multiple icons ico file) - 2,16,256, ICO_TRUE_COLOR, ICO_XP_COLOR or ICO_MAX_COLOR
 * @param int $icoSize Icon width (For multiple icons ico file) or ICO_MAX_SIZE
 * @param int $alphaBgR Background color R value for alpha-channel images (Default is White)
 * @param int $alphaBgG Background color G value for alpha-channel images (Default is White)
 * @param int $alphaBgB Background color B value for alpha-channel images (Default is White)
 * @return resource Image resource or false on error
 */
function imageCreateFromExeIco($filename,$icoIndex,$icoColorCount=16,$icoSize=16,$alphaBgR=255,$alphaBgG=255,$alphaBgB=255)
{
 $ok=saveExeIcon($filename,"icotemp.dat",$icoIndex);
 if(!$ok):
  $im=false;
 else:
   $im=imageCreateFromIco("icotemp.dat",$icoColorCount,$icoSize,$alphaBgR,$alphaBgG,$alphaBgB);
   unlink("icotemp.dat");
 endif;
 return $im;
};


/**
 * Saves icon(s) from the exe file
 * @global int $jpexs_StartOfRsrc Internal reserved variable
 * @global int $jpexs_ImageBase Internal reserved variable
 * @global int $jpexs_ResVirtualAddress Internal reserved variable
 * @param string $filename Target exefile
 * @param string $icoFileNameOrPath Filename to save ico or path (Default "") Path if you want more than 1 icon. If "", the filename is "$icoIndex.ico"
 * @param int|array $iconIndex Index(es) of the icon in exefile  (Default -1) If -1, all icons are saved, Can be an array of indexes.
 * @return boolean True on successful save
 */
function saveExeIcon($filename,$icoFileNameOrPath="",$iconIndex=-1) /*-1 for all,or can be array*/
{
  global $jpexs_f,$jpexs_StartOfRsrc,$jpexs_ImageBase,$jpexs_ResVirtualAddress;
  $jpexs_f=fopen($filename,"r");
  $MZ=fread($jpexs_f,2);
  if($MZ!="MZ") NotValidExe();
  fseek($jpexs_f,60);
  $OffsetToNewHeader=jpexs_freaddword($jpexs_f);
  fseek($jpexs_f,$OffsetToNewHeader);
  $PE=fread($jpexs_f,2);
  if($PE!="PE") NotValidExe();
  fread($jpexs_f,4);
  $NumberOfSections=jpexs_freadword($jpexs_f);
  fseek($jpexs_f,ftell($jpexs_f)+12);
  $SizeOfOptionalHeader=jpexs_freadword($jpexs_f);
  $PosMagic=ftell($jpexs_f)+2;
  fseek($jpexs_f,$PosMagic+$SizeOfOptionalHeader);

  for($p=0;$p<$NumberOfSections;$p++):
    $SectionName[$p]=trim(fread($jpexs_f,8));
    $VirtualSize[$p]=jpexs_freaddword($jpexs_f);
    $VirtualAddress[$p]=jpexs_freaddword($jpexs_f);
    $PhysicalSize[$p]=jpexs_freaddword($jpexs_f);
    $PhysicalOffset[$p]=jpexs_freaddword($jpexs_f);
    fread($jpexs_f,16);
    if($SectionName[$p]==".rsrc"):
      $jpexs_ResVirtualAddress=$VirtualAddress[$p];
      fseek($jpexs_f,$PhysicalOffset[$p]);
      $jpexs_StartOfRsrc=$PhysicalOffset[$p];
      jpexs_readResDirectoryEntry($R,$PhysicalOffset[$p]);
      $IconCount=null;
      $Ikona=null;
      while (list ($key, $val) = each ($R["Subdir"])):
        if($key==14):
          $r=0;
          while (list ($key2, $val2) = each ($R["Subdir"][$key]["Subdir"])):
             while (list ($key3, $val3) = each ($R["Subdir"][$key]["Subdir"][$key2]["Subdir"])):
               fseek($jpexs_f,$val3["DataOffset"]);
               $Reserved=jpexs_freadword($jpexs_f);
               $Type=jpexs_freadword($jpexs_f);
               $ic=jpexs_freadword($jpexs_f);
               $IconCount[]=$ic;
               for($s=0;$s<$ic;$s++)
                {
                 $Ikona[$r][$s]["Width"]=jpexs_freadbyte($jpexs_f);
                 $Ikona[$r][$s]["Height"]=jpexs_freadbyte($jpexs_f);
                 $Ikona[$r][$s]["ColorCount"]=jpexs_freadword($jpexs_f);
                 $Ikona[$r][$s]["Planes"]=jpexs_freadword($jpexs_f);
                 $Ikona[$r][$s]["BitCount"]=jpexs_freadword($jpexs_f);
                 $Ikona[$r][$s]["BytesInRes"]=jpexs_freaddword($jpexs_f);
                 $Ikona[$r][$s]["IconId"]=jpexs_freadword($jpexs_f);
                };
               fseek($jpexs_f,$val3["DataOffset"]);
               $r++;
             endwhile;
          endwhile;
        endif;
      endwhile;

      reset ($R["Subdir"]);

      while (list ($key, $val) = each ($R["Subdir"])):
        if($key==3):
          while (list ($key2, $val2) = each ($R["Subdir"][$key]["Subdir"])):
          for($r=0;$r<count($Ikona);$r++):
           for($s=0;$s<count($Ikona[$r]);$s++):
             while (list ($key3, $val3) = each ($R["Subdir"][$key]["Subdir"][$Ikona[$r][$s]["IconId"]]["Subdir"])):
               if(($iconIndex==$r)or($iconIndex==-1)or((is_array($iconIndex))and(in_array($r,$iconIndex)))):
                 fseek($jpexs_f,$val3["DataOffset"]);
                 $Ikona[$r][$s]["Data"]=fread($jpexs_f,$val3["DataSize"]);
                 $Ikona[$r][$s]["DataSize"]=$val3["DataSize"];
               endif;
             endwhile;
           endfor;
           endfor;
          endwhile;
        endif;
      endwhile;
      $ok=false;
      for($r=0;$r<count($Ikona);$r++):
        if(($iconIndex==$r)or($iconIndex==-1)or((is_array($iconIndex))and(in_array($r,$iconIndex)))):
          $savefile=$icoFileNameOrPath;
          if($icoFileNameOrPath=="")
           {
             $savefile="$r.ico";
           }
           else
           {
            if(($iconIndex==-1)or(is_array($iconIndex)))
              $savefile=$icoFileNameOrPath."$r.ico";
           };
          $f2=fopen($savefile,"w");
          fwrite($f2,jpexs_inttoword(0));
          fwrite($f2,jpexs_inttoword(1));
          fwrite($f2,jpexs_inttoword(count($Ikona[$r])));
          $Offset=6+16*count($Ikona[$r]);
          for($s=0;$s<count($Ikona[$r]);$s++):
            fwrite($f2,jpexs_inttobyte($Ikona[$r][$s]["Width"]));
            fwrite($f2,jpexs_inttobyte($Ikona[$r][$s]["Height"]));
            fwrite($f2,jpexs_inttoword($Ikona[$r][$s]["ColorCount"]));
            fwrite($f2,jpexs_inttoword($Ikona[$r][$s]["Planes"]));
            fwrite($f2,jpexs_inttoword($Ikona[$r][$s]["BitCount"]));
            fwrite($f2,jpexs_inttodword($Ikona[$r][$s]["BytesInRes"]));
            fwrite($f2,jpexs_inttodword($Offset));
            $Offset+=$Ikona[$r][$s]["DataSize"];
          endfor;
          for($s=0;$s<count($Ikona[$r]);$s++):
            fwrite($f2,$Ikona[$r][$s]["Data"]);
          endfor;
          fclose($f2);
          $ok=true;
        endif;
      endfor;
      return $ok;
    endif;
  endfor;

  fclose($jpexs_f);
};

/**
 * Internal function for reading exe icons
 */
function jpexs_readResDirectoryEntry(&$parentRes,$offset)
{
global $jpexs_f,$jpexs_StartOfRsrc,$jpexs_ImageBase,$jpexs_ResVirtualAddress;
$lastPos=ftell($jpexs_f);
$Res=null;
fseek($jpexs_f,$offset);
//IMAGE_RESOURCE_DIRECTORY
      $Characteristics=jpexs_freaddword($jpexs_f);
      $TimeDateStamp=jpexs_freaddword($jpexs_f);
      $MajorVersion=jpexs_freadword($jpexs_f);
      $MinorVersion=jpexs_freadword($jpexs_f);
      $NumberOfNamedEntries=jpexs_freadword($jpexs_f);
      $NumberOfIdEntries=jpexs_freadword($jpexs_f);
      for($q=0;$q<$NumberOfNamedEntries+$NumberOfIdEntries;$q++):
        //IMAGE_RESOURCE_DIRECTORY_ENTRY
        $ResName=jpexs_freaddword($jpexs_f);
        $lastPos2=ftell($jpexs_f);
        if($ResName>=0x80000000):
          //String Name
          $ResNameOffset=$ResName-0x80000000;
          fseek($jpexs_f,$jpexs_StartOfRsrc+$ResNameOffset);
          $StringLength=jpexs_freadword($jpexs_f);
          $Identificator=(fread($jpexs_f,$StringLength*2));
          fseek($jpexs_f,$lastPos2);
        else:
          //Integer Id
          $Identificator=$ResName;
        endif;

        $ResOffsetToData=jpexs_freaddword($jpexs_f);
        if($ResOffsetToData>=0x80000000):
          $SubResOffset=$ResOffsetToData-0x80000000;
          jpexs_readResDirectoryEntry($Res["$Identificator"],$jpexs_StartOfRsrc+$SubResOffset);
        else:
          $RawDataOffset=$ResOffsetToData;
          $lastPos2=ftell($jpexs_f);
          fseek($jpexs_f,$jpexs_StartOfRsrc+$RawDataOffset);
          //IMAGE_RESOURCE_DATA_ENTRY
          $OffsetToData=jpexs_freaddword($jpexs_f);
          $Res["$Identificator"]["DataOffset"]=$jpexs_StartOfRsrc-$jpexs_ResVirtualAddress+$OffsetToData;
          $Res["$Identificator"]["DataSize"]=jpexs_freaddword($jpexs_f);
          $CodePage=jpexs_freaddword($jpexs_f);
          $Reserved=jpexs_freaddword($jpexs_f);
          fseek($jpexs_f,$lastPos2);
        endif;
      endfor;
fseek($jpexs_f,$lastPos);
$parentRes["Subdir"]=$Res;
};

/**
 * Creates ico file from image resource(s)
 * @param resource|array $images Target Image resource (Can be array of image resources)
 * @param string $filename Target ico file to save icon to, If ommited or "", image is written to snadard output - use header("Content-type: image/x-icon"); */
function imageIco($images,$filename="")
{

if(is_array($images))
{
$ImageCount=count($images);
$Image=$images;
}
else
{
$Image[0]=$images;
$ImageCount=1;
};


$WriteToFile=false;

if($filename!="")
{
$WriteToFile=true;
};


$ret="";

$ret.=jpexs_inttoword(0); //PASSWORD
$ret.=jpexs_inttoword(1); //SOURCE
$ret.=jpexs_inttoword($ImageCount); //ICONCOUNT


for($q=0;$q<$ImageCount;$q++)
{
$img=$Image[$q];

$Width=imagesx($img);
$Height=imagesy($img);

$ColorCount=imagecolorstotal($img);

$Transparent=imagecolortransparent($img);
$IsTransparent=$Transparent!=-1;


if($IsTransparent) $ColorCount--;

if($ColorCount==0) {$ColorCount=0; $BitCount=24;};
if(($ColorCount>0)and($ColorCount<=2)) {$ColorCount=2; $BitCount=1;};
if(($ColorCount>2)and($ColorCount<=16)) { $ColorCount=16; $BitCount=4;};
if(($ColorCount>16)and($ColorCount<=256)) { $ColorCount=0; $BitCount=8;};





//ICONINFO:
$ret.=jpexs_inttobyte($Width);//
$ret.=jpexs_inttobyte($Height);//
$ret.=jpexs_inttobyte($ColorCount);//
$ret.=jpexs_inttobyte(0);//RESERVED

$Planes=0;
if($BitCount>=8) $Planes=1;

$ret.=jpexs_inttoword($f,$Planes);//PLANES
if($BitCount>=8) $WBitCount=$BitCount;
if($BitCount==4) $WBitCount=0;
if($BitCount==1) $WBitCount=0;
$ret.=jpexs_inttoword($WBitCount);//BITS

$Zbytek=(4-($Width/(8/$BitCount))%4)%4;
$ZbytekMask=(4-($Width/8)%4)%4;

$PalSize=0;

$Size=40+($Width/(8/$BitCount)+$Zbytek)*$Height+(($Width/8+$ZbytekMask) * $Height);
if($BitCount<24)
 $Size+=pow(2,$BitCount)*4;
$IconId=1;
$ret.=jpexs_inttodword($Size); //SIZE
$OffSet=6+16*$ImageCount+$FullSize;
$ret.=jpexs_inttodword(6+16*$ImageCount+$FullSize);//OFFSET
$FullSize+=$Size;
//-------------

};


for($q=0;$q<$ImageCount;$q++)
{
$img=$Image[$q];
$Width=imagesx($img);
$Height=imagesy($img);
$ColorCount=imagecolorstotal($img);

$Transparent=imagecolortransparent($img);
$IsTransparent=$Transparent!=-1;

if($IsTransparent) $ColorCount--;
if($ColorCount==0) {$ColorCount=0; $BitCount=24;};
if(($ColorCount>0)and($ColorCount<=2)) {$ColorCount=2; $BitCount=1;};
if(($ColorCount>2)and($ColorCount<=16)) { $ColorCount=16; $BitCount=4;};
if(($ColorCount>16)and($ColorCount<=256)) { $ColorCount=0; $BitCount=8;};



//ICONS
$ret.=jpexs_inttodword(40);//HEADSIZE
$ret.=jpexs_inttodword($Width);//
$ret.=jpexs_inttodword(2*$Height);//
$ret.=jpexs_inttoword(1); //PLANES
$ret.=jpexs_inttoword($BitCount);   //
$ret.=jpexs_inttodword(0);//Compress method


$ZbytekMask=($Width/8)%4;

$Zbytek=($Width/(8/$BitCount))%4;
$Size=($Width/(8/$BitCount)+$Zbytek)*$Height+(($Width/8+$ZbytekMask) * $Height);

$ret.=jpexs_inttodword($Size);//SIZE

$ret.=jpexs_inttodword(0);//HPIXEL_M
$ret.=jpexs_inttodword(0);//V_PIXEL_M
$ret.=jpexs_inttodword($ColorCount); //UCOLORS
$ret.=jpexs_inttodword(0); //DCOLORS
//---------------


$CC=$ColorCount;
if($CC==0) $CC=256;

if($BitCount<24)
{
 $ColorTotal=imagecolorstotal($img);
 if($IsTransparent) $ColorTotal--;

 for($p=0;$p<$ColorTotal;$p++)
  {
   $color=imagecolorsforindex($img,$p);
   $ret.=jpexs_inttobyte($color["blue"]);
   $ret.=jpexs_inttobyte($color["green"]);
   $ret.=jpexs_inttobyte($color["red"]);
   $ret.=jpexs_inttobyte(0); //RESERVED
  };

 $CT=$ColorTotal;
 for($p=$ColorTotal;$p<$CC;$p++)
  {
   $ret.=jpexs_inttobyte(0);
   $ret.=jpexs_inttobyte(0);
   $ret.=jpexs_inttobyte(0);
   $ret.=jpexs_inttobyte(0); //RESERVED
  };
};






if($BitCount<=8)
{

 for($y=$Height-1;$y>=0;$y--)
 {
  $bWrite="";
  for($x=0;$x<$Width;$x++)
   {
   $color=imagecolorat($img,$x,$y);
   if($color==$Transparent)
    $color=imagecolorexact($img,0,0,0);
   if($color==-1) $color=0;
   if($color>pow(2,$BitCount)-1) $color=0;

   $bWrite.=jpexs_decbinx($color,$BitCount);
   if(strlen($bWrite)==8)
    {
     $ret.=jpexs_inttobyte(bindec($bWrite));
     $bWrite="";
    };
   };

  if((strlen($bWrite)<8)and(strlen($bWrite)!=0))
    {
     $sl=strlen($bWrite);
     for($t=0;$t<8-$sl;$t++)
      $sl.="0";
     $ret.=jpexs_inttobyte(bindec($bWrite));
    };
  for($z=0;$z<$Zbytek;$z++)
   $ret.=jpexs_inttobyte(0);
 };
};



if($BitCount>=24)
{
 for($y=$Height-1;$y>=0;$y--)
 {
  for($x=0;$x<$Width;$x++)
   {
   $color=imagecolorsforindex($img,imagecolorat($img,$x,$y));
   $ret.=jpexs_inttobyte($color["blue"]);
   $ret.=jpexs_inttobyte($color["green"]);
   $ret.=jpexs_inttobyte($color["red"]);
   if($BitCount==32)
    $ret.=jpexs_inttobyte(0);//Alpha for ICO_XP_COLORS
   };
  for($z=0;$z<$Zbytek;$z++)
   $ret.=jpexs_inttobyte(0);
 };
};


//MASK

 for($y=$Height-1;$y>=0;$y--)
 {
  $byteCount=0;
  $bOut="";
  for($x=0;$x<$Width;$x++)
   {
    if(($Transparent!=-1)and(imagecolorat($img,$x,$y)==$Transparent))
     {
      $bOut.="1";
     }
     else
     {
      $bOut.="0";
     };
   };
  for($p=0;$p<strlen($bOut);$p+=8)
  {
   $byte=bindec(substr($bOut,$p,8));
   $byteCount++;
   $ret.=jpexs_inttobyte($byte);
  };
 $Zbytek=$byteCount%4;
  for($z=0;$z<$Zbytek;$z++)
   {
   $ret.=jpexs_inttobyte(0xff);
   };
 };

//------------------

};//q





if($WriteToFile)
{
 $f=fopen($filename,"w");
 fwrite($f,$ret);
 fclose($f);
}
else
{
 echo $ret;
};

};




/*
* Internal functions:
*-------------------------
* jpexs_inttobyte($n) - returns chr(n)
* jpexs_inttodword($n) - returns dword (n)
* jpexs_inttoword($n) - returns word(n)
* jpexs_freadbyte($file) - reads 1 byte from $file
* jpexs_freadword($file) - reads 2 bytes (1 word) from $file
* jpexs_freaddword($file) - reads 4 bytes (1 dword) from $file
* jpexs_freadlngint($file) - same as freaddword($file)
* jpexs_decbin8($d) - returns binary string of d zero filled to 8
* jpexs_RetBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
* jpexs_freadbits($file,$count) - reads next $count bits from $file
*/


function jpexs_decbin8($d)
{
return jpexs_decbinx($d,8);
};

function jpexs_decbinx($d,$n)
{
$bin=decbin($d);
$sbin=strlen($bin);
for($j=0;$j<$n-$sbin;$j++)
 $bin="0$bin";
return $bin;
};

function jpexs_retBits($byte,$start,$len)
{
$bin=jpexs_decbin8($byte);
$r=bindec(substr($bin,$start,$len));
return $r;

};



$jpexs_currentBit=0;
function jpexs_freadbits($f,$count)
{
 global $jpexs_currentBit,$jpexs_SMode;
 $Byte=jpexs_freadbyte($f);
 $LastCBit=$jpexs_currentBit;
 $jpexs_currentBit+=$count;
 if($jpexs_currentBit==8)
  {
   $jpexs_currentBit=0;
  }
 else
  {
   fseek($f,ftell($f)-1);
  };
 return jpexs_retBits($Byte,$LastCBit,$count);
};


function jpexs_freadbyte($f)
{
 return ord(fread($f,1));
};

function jpexs_freadword($f)
{
 $b1=jpexs_freadbyte($f);
 $b2=jpexs_freadbyte($f);
 return $b2*256+$b1;
};


function jpexs_freadlngint($f)
{
return jpexs_freaddword($f);
};

function jpexs_freaddword($f)
{
 $b1=jpexs_freadword($f);
 $b2=jpexs_freadword($f);
 return $b2*65536+$b1;
};

function jpexs_inttobyte($n)
{
return chr($n);
};

function jpexs_inttodword($n)
{
return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
};

function jpexs_inttoword($n)
 {
 return chr($n & 255).chr(($n >> 8) & 255);
 };

?>