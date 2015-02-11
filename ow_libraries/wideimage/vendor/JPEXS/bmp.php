<?php
/**
 * @package com.jpexs.image.bmp
 *
 * JPEXS BMP Image functions
 * @version 1.1
 * @author JPEXS
 * @copyright (c) JPEXS 2004-2009
 *
 * Webpage: http://www.jpexs.com
 * Email: jpexs@jpexs.com
 *
 * If you like my script, you can donate... visit my webpages or email me for more info.
 *
 *        Version changes:
 *                v1.1 - redesigned sourcecode, phpdoc included, all internal functions and global variables have prefix "jpexs_"
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



/**
 * Creates new BMP file from image resource
 * @param resource $img Image resource to convert
 * @param string $file File to save image to. If ommited or "", file is written to standard output
 * @param boolean $useRLE When true, file will be saved with RLE compression (EXPERIMENTAL)
 * @return boolean True when successfully writen to specified file
 */
function imagebmp($img,$file="",$useRLE=false)
{


	$ColorCount=imagecolorstotal($img);

	$Transparent=imagecolortransparent($img);
	$IsTransparent=$Transparent!=-1;


	if($IsTransparent) $ColorCount--;

	if($ColorCount==0) {$ColorCount=0; $BitCount=24; $palsize = 0; };
	if(($ColorCount>0)and($ColorCount<=2)) {$ColorCount=2; $BitCount=1;};
	if(($ColorCount>2)and($ColorCount<=16)) { $ColorCount=16; $BitCount=4;};
	if(($ColorCount>16)and($ColorCount<=256)) { $ColorCount=0; $BitCount=8;};
	
	$Width=imagesx($img);
	$Height=imagesy($img);

	$Zbytek=(4-($Width/(8/$BitCount))%4)%4;

	if($BitCount<24) $palsize=pow(2,$BitCount)*4;

	$size=(floor($Width/(8/$BitCount))+$Zbytek)*$Height+54;
	$size+=$palsize;
	$offset=54+$palsize;

	// Bitmap File Header
	$ret = 'BM';                        // header (2b)
	$ret .= jpexs_int_to_dword($size);        // size of file (4b)
	$ret .= jpexs_int_to_dword(0);        // reserved (4b)
	$ret .= jpexs_int_to_dword($offset);        // byte location in the file which is first byte of IMAGE (4b)
	// Bitmap Info Header
	$ret .= jpexs_int_to_dword(40);        // Size of BITMAPINFOHEADER (4b)
	$ret .= jpexs_int_to_dword($Width);        // width of bitmap (4b)
	$ret .= jpexs_int_to_dword($Height);        // height of bitmap (4b)
	$ret .= jpexs_int_to_word(1);        // biPlanes = 1 (2b)
	$ret .= jpexs_int_to_word($BitCount);        // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
	$ret .= jpexs_int_to_dword($useRLE);        // RLE COMPRESSION (4b)
	$ret .= jpexs_int_to_dword(0);        // width x height (4b)
	$ret .= jpexs_int_to_dword(0);        // biXPelsPerMeter (4b)
	$ret .= jpexs_int_to_dword(0);        // biYPelsPerMeter (4b)
	$ret .= jpexs_int_to_dword(0);        // Number of palettes used (4b)
	$ret .= jpexs_int_to_dword(0);        // Number of important colour (4b)
	// image data
	
	$retd = "";
	
	$CC=$ColorCount;
	$sl1=strlen($ret);
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
				$bWrite.=jpexs_decbinx($color,$BitCount);
				if(strlen($bWrite)==8)
				{
					$retd.=jpexs_inttobyte(bindec($bWrite));
					$bWrite="";
				};
			};

			if((strlen($bWrite)<8)and(strlen($bWrite)!=0))
			{
				$sl=strlen($bWrite);
				for($t=0;$t<8-$sl;$t++)
				$sl.="0";
				$retd.=jpexs_inttobyte(bindec($bWrite));
			};
			for($z=0;$z<$Zbytek;$z++)
			$retd.=jpexs_inttobyte(0);
		};
	};

	if(($useRLE)and($BitCount==8))
	{
		for($t=0;$t<strlen($retd);$t+=4)
		{
			if($t!=0)
			if(($t)%$Width==0)
			$ret.=chr(0).chr(0);

			if(($t+5)%$Width==0)
			{
				$ret.=chr(0).chr(5).substr($retd,$t,5).chr(0);
				$t+=1;
			}
			if(($t+6)%$Width==0)
			{
				$ret.=chr(0).chr(6).substr($retd,$t,6);
				$t+=2;
			}
			else
			{
				$ret.=chr(0).chr(4).substr($retd,$t,4);
			};
		};
		$ret.=chr(0).chr(1);
	}
	else
	{
		$ret.=$retd;
	};

	$Dopl = "";
	if($BitCount==24)
	{
		for($z=0;$z<$Zbytek;$z++)
		$Dopl.=chr(0);

		for($y=$Height-1;$y>=0;$y--)
		{
			for($x=0;$x<$Width;$x++)
			{
				$color=imagecolorsforindex($img,ImageColorAt($img,$x,$y));
				$ret.=chr($color["blue"]).chr($color["green"]).chr($color["red"]);
			}
			$ret.=$Dopl;
		};

	};

	if($file!="")
	{
		$r=($f=fopen($file,"w"));
		$r=$r and fwrite($f,$ret);
		$r=$r and fclose($f);
		return $r;
	}
	else
	{
		echo $ret;
	};
};


/**
 * Reads image from a BMP file and converts it to image resource
 * @global int $jpexs_CurrentBit Internal variable
 * @param string $file File to read BMP image from
 * @return resource Image resource or false on error
 *
 * Note:
 *  Reading RLE compressed bitmaps is EXPERIMENTAL
 *  Reading palette based bitmaps with less than 8bit palette is EXPERIMENTAL
 */
function imagecreatefrombmp($file)
{
	global  $jpexs_CurrentBit;

	$f=fopen($file,"r");
	$Header=fread($f,2);

	if($Header=="BM")
	{
		$Size=jpexs_freaddword($f);
		$Reserved1=jpexs_freadword($f);
		$Reserved2=jpexs_freadword($f);
		$FirstByteOfImage=jpexs_freaddword($f);

		$SizeBITMAPINFOHEADER=jpexs_freaddword($f);
		$Width=jpexs_freaddword($f);
		$Height=jpexs_freaddword($f);
		$biPlanes=jpexs_freadword($f);
		$biBitCount=jpexs_freadword($f);
		$RLECompression=jpexs_freaddword($f);
		$WidthxHeight=jpexs_freaddword($f);
		$biXPelsPerMeter=jpexs_freaddword($f);
		$biYPelsPerMeter=jpexs_freaddword($f);
		$NumberOfPalettesUsed=jpexs_freaddword($f);
		$NumberOfImportantColors=jpexs_freaddword($f);

		if($biBitCount<24)
		{
			$img=imagecreate($Width,$Height);
			$Colors=pow(2,$biBitCount);
			for($p=0;$p<$Colors;$p++)
			{
				$B=jpexs_freadbyte($f);
				$G=jpexs_freadbyte($f);
				$R=jpexs_freadbyte($f);
				$Reserved=jpexs_freadbyte($f);
				$Palette[]=imagecolorallocate($img,$R,$G,$B);
			};




			if($RLECompression==0)
			{
				$Zbytek=(4-ceil(($Width/(8/$biBitCount)))%4)%4;

				for($y=$Height-1;$y>=0;$y--)
				{
					$jpexs_CurrentBit=0;
					for($x=0;$x<$Width;$x++)
					{
						$C=jpexs_freadbits($f,$biBitCount);
						imagesetpixel($img,$x,$y,$Palette[$C]);
					};
					if($jpexs_CurrentBit!=0) {jpexs_freadbyte($f);};
					for($g=0;$g<$Zbytek;$g++)
					jpexs_freadbyte($f);
				};

			};
		};


		if($RLECompression==1) //$BI_RLE8
		{
			$y=$Height;

			$pocetb=0;

			while(true)
			{
				$y--;
				$prefix=jpexs_freadbyte($f);
				$suffix=jpexs_freadbyte($f);
				$pocetb+=2;

				$echoit=false;

				if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
				if(($prefix==0)and($suffix==1)) break;
				if(feof($f)) break;

				while(!(($prefix==0)and($suffix==0)))
				{
					if($prefix==0)
					{
						$pocet=$suffix;
						$Data.=fread($f,$pocet);
						$pocetb+=$pocet;
						if($pocetb%2==1) {jpexs_freadbyte($f); $pocetb++;};
					};
					if($prefix>0)
					{
						$pocet=$prefix;
						for($r=0;$r<$pocet;$r++)
						$Data.=chr($suffix);
					};
					$prefix=jpexs_freadbyte($f);
					$suffix=jpexs_freadbyte($f);
					$pocetb+=2;
					if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
				};

				for($x=0;$x<strlen($Data);$x++)
				{
					imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
				};
				$Data="";

			};

		};


		if($RLECompression==2) //$BI_RLE4
		{
			$y=$Height;
			$pocetb=0;

			/*while(!feof($f))
			 echo freadbyte($f)."_".freadbyte($f)."<BR>";*/
			while(true)
			{
				//break;
				$y--;
				$prefix=jpexs_freadbyte($f);
				$suffix=jpexs_freadbyte($f);
				$pocetb+=2;

				$echoit=false;

				if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>";
				if(($prefix==0)and($suffix==1)) break;
				if(feof($f)) break;

				while(!(($prefix==0)and($suffix==0)))
				{
					if($prefix==0)
					{
						$pocet=$suffix;

						$jpexs_CurrentBit=0;
						for($h=0;$h<$pocet;$h++)
						$Data.=chr(jpexs_freadbits($f,4));
						if($jpexs_CurrentBit!=0) jpexs_freadbits($f,4);
						$pocetb+=ceil(($pocet/2));
						if($pocetb%2==1) {jpexs_freadbyte($f); $pocetb++;};
					};
					if($prefix>0)
					{
						$pocet=$prefix;
						$i=0;
						for($r=0;$r<$pocet;$r++)
						{
							if($i%2==0)
							{
								$Data.=chr($suffix%16);
							}
							else
							{
								$Data.=chr(floor($suffix/16));
							};
							$i++;
						};
					};
					$prefix=jpexs_freadbyte($f);
					$suffix=jpexs_freadbyte($f);
					$pocetb+=2;
					if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>";
				};

				for($x=0;$x<strlen($Data);$x++)
				{
					imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]);
				};
				$Data="";

			};

		};


		if($biBitCount==24)
		{
			$img=imagecreatetruecolor($Width,$Height);
			$Zbytek=$Width%4;

			for($y=$Height-1;$y>=0;$y--)
			{
				for($x=0;$x<$Width;$x++)
				{
					$B=jpexs_freadbyte($f);
					$G=jpexs_freadbyte($f);
					$R=jpexs_freadbyte($f);
					$color=imagecolorexact($img,$R,$G,$B);
					if($color==-1) $color=imagecolorallocate($img,$R,$G,$B);
					imagesetpixel($img,$x,$y,$color);
				}
				for($z=0;$z<$Zbytek;$z++)
				jpexs_freadbyte($f);
			};
		};
		return $img;

	}
	else{
		return false;
	}


	fclose($f);


};





/*
 * Internal functions:
 *-------------------------
 *
 * jpexs_freadbyte($file) - reads 1 byte from $file
 * jpexs_freadword($file) - reads 2 bytes (1 word) from $file
 * jpexs_freaddword($file) - reads 4 bytes (1 dword) from $file
 * jpexs_freadlngint($file) - same as freaddword($file)
 * jpexs_decbin8($d) - returns binary string of d zero filled to 8
 * jpexs_retBits($byte,$start,$len) - returns bits $start->$start+$len from $byte
 * jpexs_freadbits($file,$count) - reads next $count bits from $file
 * jpexs_int_to_dword($n) - returns 4 byte representation of $n
 * jpexs_int_to_word($n) - returns 2 byte representation of $n
 */

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



function jpexs_retBits($byte,$start,$len)
{
	$bin=jpexs_decbin8($byte);
	$r=bindec(substr($bin,$start,$len));
	return $r;

};



$jpexs_CurrentBit=0;
function jpexs_freadbits($f,$count)
{
	global $jpexs_CurrentBit,$SMode;
	$Byte=jpexs_freadbyte($f);
	$LastCBit=$jpexs_CurrentBit;
	$jpexs_CurrentBit+=$count;
	if($jpexs_CurrentBit==8)
	{
		$jpexs_CurrentBit=0;
	}
	else
	{
		fseek($f,ftell($f)-1);
	};
	return jpexs_retBits($Byte,$LastCBit,$count);
};



function jpexs_int_to_dword($n)
{
	return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
}
function jpexs_int_to_word($n)
{
	return chr($n & 255).chr(($n >> 8) & 255);
}


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

function jpexs_inttobyte($n)
{
	return chr($n);
};

?>