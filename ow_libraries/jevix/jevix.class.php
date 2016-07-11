<?php

class Jevix
{
    const PRINTABLE = 0x1;
    const ALPHA = 0x2;
    const LAT = 0x4;
    const RUS = 0x8;
    const NUMERIC = 0x10;
    const SPACE = 0x20;
    const NAME = 0x40;
    const URL = 0x100;
    const NOPRINT = 0x200;
    const PUNCTUATUON = 0x400;
    const HTML_QUOTE = 0x2000;
    const TAG_QUOTE = 0x4000;
    const QUOTE_CLOSE = 0x8000;
    const NL = 0x10000;
    const QUOTE_OPEN = 0;
    const STATE_TEXT = 0;
    const STATE_TAG_PARAMS = 1;
    const STATE_TAG_PARAM_VALUE = 2;
    const STATE_INSIDE_TAG = 3;
    const STATE_INSIDE_NOTEXT_TAG = 4;
    const STATE_INSIDE_PREFORMATTED_TAG = 5;

    public $blackListMode = false;
    public $commonTagParamRules = array();
    public $tagsRules = array();
    public $entities1 = array();
    public $entities2 = array();
    public $dash = " — ";
    public $apostrof = "’";
    public $dotes = "…";
    public $nl = "\r\n";
    public $mediaSrcValidate = false;
    public $mediaValidSrc = array();
    public $forceShortTags = array('br', 'img', 'input', 'hr', 'link');
    protected $text;
    protected $textBuf;
    protected $textLen = 0;
    protected $curPos;
    protected $curCh;
    protected $curChOrd;
    protected $curChClass;
    protected $curParentTag;
    protected $states;
    protected $quotesOpened = 0;
    protected $brAdded = 0;
    protected $state;
    protected $tagsStack;
    protected $openedTag;
    protected $isXHTMLMode = false;
    protected $noTypoMode = true;
    public $isAutoLinkMode = false;
    public $outBuffer = '';
    public $errors;

    const TR_TAG_LIST = 1;
    const TR_PARAM_ALLOWED = 2;
    const TR_PARAM_REQUIRED = 3;
    const TR_TAG_SHORT = 4;
    const TR_TAG_CUT = 5;
    const TR_TAG_CHILD = 6;
    const TR_TAG_CONTAINER = 7;
    const TR_TAG_CHILD_TAGS = 8;
    const TR_TAG_PARENT = 9;
    const TR_TAG_PREFORMATTED = 10;
    const TR_PARAM_AUTO_ADD = 11;
    const TR_TAG_NO_TYPOGRAPHY = 12;
    const TR_TAG_IS_EMPTY = 13;
    const TR_TAG_NO_AUTO_BR = 14;

    protected $chClasses = array(0 => 512, 1 => 512, 2 => 512, 3 => 512, 4 => 512, 5 => 512, 6 => 512, 7 => 512, 8 => 512, 9 => 32, 10 => 66048, 11 => 512, 12 => 512, 13 => 66048, 14 => 512, 15 => 512, 16 => 512, 17 => 512, 18 => 512, 19 => 512, 20 => 512, 21 => 512, 22 => 512, 23 => 512, 24 => 512, 25 => 512, 26 => 512, 27 => 512, 28 => 512, 29 => 512, 30 => 512, 31 => 512, 32 => 32, 97 => 71, 98 => 71, 99 => 71, 100 => 71, 101 => 71, 102 => 71, 103 => 71, 104 => 71, 105 => 71, 106 => 71, 107 => 71, 108 => 71, 109 => 71, 110 => 71, 111 => 71, 112 => 71, 113 => 71, 114 => 71, 115 => 71, 116 => 71, 117 => 71, 118 => 71, 119 => 71, 120 => 71, 121 => 71, 122 => 71, 65 => 71, 66 => 71, 67 => 71, 68 => 71, 69 => 71, 70 => 71, 71 => 71, 72 => 71, 73 => 71, 74 => 71, 75 => 71, 76 => 71, 77 => 71, 78 => 71, 79 => 71, 80 => 71, 81 => 71, 82 => 71, 83 => 71, 84 => 71, 85 => 71, 86 => 71, 87 => 71, 88 => 71, 89 => 71, 90 => 71, 1072 => 11, 1073 => 11, 1074 => 11, 1075 => 11, 1076 => 11, 1077 => 11, 1078 => 11, 1079 => 11, 1080 => 11, 1081 => 11, 1082 => 11, 1083 => 11, 1084 => 11, 1085 => 11, 1086 => 11, 1087 => 11, 1088 => 11, 1089 => 11, 1090 => 11, 1091 => 11, 1092 => 11, 1093 => 11, 1094 => 11, 1095 => 11, 1096 => 11, 1097 => 11, 1098 => 11, 1099 => 11, 1100 => 11, 1101 => 11, 1102 => 11, 1103 => 11, 1040 => 11, 1041 => 11, 1042 => 11, 1043 => 11, 1044 => 11, 1045 => 11, 1046 => 11, 1047 => 11, 1048 => 11, 1049 => 11, 1050 => 11, 1051 => 11, 1052 => 11, 1053 => 11, 1054 => 11, 1055 => 11, 1056 => 11, 1057 => 11, 1058 => 11, 1059 => 11, 1060 => 11, 1061 => 11, 1062 => 11, 1063 => 11, 1064 => 11, 1065 => 11, 1066 => 11, 1067 => 11, 1068 => 11, 1069 => 11, 1070 => 11, 1071 => 11, 48 => 337, 49 => 337, 50 => 337, 51 => 337, 52 => 337, 53 => 337, 54 => 337, 55 => 337, 56 => 337, 57 => 337, 34 => 57345, 39 => 16385, 46 => 1281, 44 => 1025, 33 => 1025, 63 => 1281, 58 => 1025, 59 => 1281, 1105 => 11, 1025 => 11, 47 => 257, 38 => 257, 37 => 257, 45 => 257, 95 => 257, 61 => 257, 43 => 257, 35 => 257, 124 => 257,);

    protected function &strToArray( $str )
    {
        $chars = null;
        preg_match_all('/./su', $str, $chars);
        return $chars[0];
    }

    function parse( $text )
    {
        $this->curPos = -1;
        $this->curCh = null;
        $this->curChOrd = 0;
        $this->state = self::STATE_TEXT;
        $this->states = array();
        $this->quotesOpened = 0;
        $this->noTypoMode = false;
        $this->text = $text;
        $this->textBuf = $this->strToArray($this->text);
        $this->textLen = count($this->textBuf);
        $this->getCh();
        $content = '';
        $this->outBuffer = '';
        $this->brAdded = 0;
        $this->tagsStack = array();
        $this->openedTag = null;
        $this->errors = array();

        $this->skipSpaces();

        $this->anyThing($content);

        if ( mb_strstr($content, '<!--') && (mb_substr_count($content, '<!--') > mb_substr_count($content, '-->')) )
        {
            $content .= '-->';
        }

        return $content;
    }

    protected function getCh()
    {
        return $this->goToPosition($this->curPos + 1);
    }

    protected function goToPosition( $position )
    {
        $this->curPos = $position;
        if ( $this->curPos < $this->textLen )
        {
            $this->curCh = $this->textBuf[$this->curPos];
            $this->curChOrd = uniord($this->curCh);
            $this->curChClass = $this->getCharClass($this->curChOrd);
        }
        else
        {
            $this->curCh = null;
            $this->curChOrd = 0;
            $this->curChClass = 0;
        }
        return $this->curCh;
    }

    protected function saveState()
    {
        $state = array(
            'pos' => $this->curPos,
            'ch' => $this->curCh,
            'ord' => $this->curChOrd,
            'class' => $this->curChClass,
        );

        $this->states[] = $state;
        return count($this->states) - 1;
    }

    protected function restoreState( $index = null )
    {
        if ( !count($this->states) )
        {
            throw new Exception('The end of stack');
        }

        if ( $index == null )
        {
            $state = array_pop($this->states);
        }
        else
        {
            if ( !isset($this->states[$index]) )
            {
                throw new Exception('Invalid stack index');
            }
            $state = $this->states[$index];
            $this->states = array_slice($this->states, 0, $index);
        }

        $this->curPos = $state['pos'];
        $this->curCh = $state['ch'];
        $this->curChOrd = $state['ord'];
        $this->curChClass = $state['class'];
    }

    protected function matchCh( $ch, $skipSpaces = false )
    {
        if ( $this->curCh == $ch )
        {
            $this->getCh();
            if ( $skipSpaces )
            {
                $this->skipSpaces();
            }
            return true;
        }

        return false;
    }

    protected function matchChClass( $chClass, $skipSpaces = false )
    {
        if ( ($this->curChClass & $chClass) == $chClass )
        {
            $ch = $this->curCh;
            $this->getCh();
            if ( $skipSpaces )
            {
                $this->skipSpaces();
            }
            return $ch;
        }

        return false;
    }

    protected function matchStr( $str, $skipSpaces = false )
    {
        $this->saveState();
        $len = mb_strlen($str, 'UTF-8');
        $test = '';
        while ( $len-- && $this->curChClass )
        {
            $test.=$this->curCh;
            $this->getCh();
        }

        if ( $test == $str )
        {
            if ( $skipSpaces )
            {
                $this->skipSpaces();
            }
            return true;
        }
        else
        {
            $this->restoreState();
            return false;
        }
    }

    protected function skipUntilCh( $ch )
    {
        $chPos = mb_strpos($this->text, $ch, $this->curPos, 'UTF-8');
        if ( $chPos )
        {
            return $this->goToPosition($chPos);
        }
        else
        {
            return false;
        }
    }

    protected function skipUntilStr( $str )
    {
        $str = $this->strToArray($str);
        $firstCh = $str[0];
        $len = count($str);
        while ( $this->curChClass )
        {
            if ( $this->curCh == $firstCh )
            {
                $this->saveState();
                $this->getCh();
                $strOK = true;
                for ( $i = 1; $i < $len; $i++ )
                {
                    if ( !$this->curChClass )
                    {
                        return false;
                    }

                    if ( $this->curCh != $str[$i] )
                    {
                        $strOK = false;
                        break;
                    }

                    $this->getCh();
                }

                if ( !$strOK )
                {
                    $this->restoreState();
                }
                else
                {
                    return true;
                }
            }

            $this->getCh();
        }
        return false;
    }

    protected function getCharClass( $ord )
    {
        return isset($this->chClasses[$ord]) ? $this->chClasses[$ord] : self::PRINTABLE;
    }

    protected function skipSpaces( &$count = 0 )
    {
        while ( $this->curChClass == self::SPACE )
        {
            $this->getCh();
            $count++;
        }
        return $count > 0;
    }

    protected function name( &$name = '', $minus = false )
    {
        $this->skipNL();

        if ( ($this->curChClass & self::LAT) == self::LAT )
        {
            $name.=$this->curCh;
            $this->getCh();
        }
        else
        {
            return false;
        }

        while ( (($this->curChClass & self::NAME) == self::NAME || ($minus && $this->curCh && !in_array($this->curCh, array(' ', '=', '>')) ) ) )
        {
            $name.=$this->curCh;
            $this->getCh();
        }

        $this->skipSpaces();
        return true;
    }

    protected function tag( &$tag, &$params, &$content, &$short )
    {
        $this->saveState();
        $params = array();
        $tag = '';
        $closeTag = '';
        $params = array();
        $short = false;

        if ( !$this->tagOpen($tag, $params, $short) )
        {
            return false;
        }

        if ( $short )
        {
            return true;
        }

        $oldState = $this->state;
        $oldNoTypoMode = $this->noTypoMode;

        if ( !empty($this->tagsRules[$tag][self::TR_TAG_PREFORMATTED]) )
        {
            $this->state = self::STATE_INSIDE_PREFORMATTED_TAG;
        }
        elseif ( !empty($this->tagsRules[$tag][self::TR_TAG_CONTAINER]) )
        {
            $this->state = self::STATE_INSIDE_NOTEXT_TAG;
        }
        elseif ( !empty($this->tagsRules[$tag][self::TR_TAG_NO_TYPOGRAPHY]) )
        {
            $this->noTypoMode = true;
            $this->state = self::STATE_INSIDE_TAG;
        }
        else
        {
            $this->state = self::STATE_INSIDE_TAG;
        }

        array_push($this->tagsStack, $tag);
        $this->openedTag = $tag;
        $content = '';
        if ( $this->state == self::STATE_INSIDE_PREFORMATTED_TAG )
        {
            $this->preformatted($content, $tag);
        }
        else
        {
            $this->anyThing($content, $tag);
        }

        array_pop($this->tagsStack);
        $this->openedTag = !empty($this->tagsStack) ? array_pop($this->tagsStack) : null;

        $isTagClose = $this->tagClose($closeTag);
        if ( $isTagClose && ($tag != $closeTag) )
        {
            $this->error("Invalid tag close $closeTag. > expected $tag");
        }

        $this->state = $oldState;
        $this->noTypoMode = $oldNoTypoMode;

        return true;
    }

    protected function preformatted( &$content = '', $insideTag = null )
    {
        while ( $this->curChClass )
        {
            if ( $this->curCh == '<' )
            {
                $tag = '';
                $this->saveState();

                $isClosedTag = $this->tagClose($tag);

                if ( $isClosedTag )
                {
                    $this->restoreState();
                }

                if ( $isClosedTag && $tag == $insideTag )
                {
                    return;
                }
            }
            $content.= isset($this->entities2[$this->curCh]) ? $this->entities2[$this->curCh] : $this->curCh;
            $this->getCh();
        }
    }

    protected function tagOpen( &$name, &$params, &$short = false )
    {
        $restore = $this->saveState();

        if ( !$this->matchCh('<') )
        {
            return false;
        }
        $this->skipSpaces();
        if ( !$this->name($name) )
        {
            $this->restoreState();
            return false;
        }
        $name = mb_strtolower($name, 'UTF-8');

        if ( $this->curCh != '>' && $this->curCh != '/' )
        {
            $this->tagParams($params);
        }

        $short = ( /* $this->curCh == '/' && */ in_array($name, $this->forceShortTags) && !empty($this->tagsRules[$name][self::TR_TAG_SHORT]) );

        $slash = $this->matchCh('/');

        if ( !$short && $slash && empty($this->tagsRules[$name][self::TR_TAG_SHORT]) )
        {
            $this->restoreState();
            return false;
        }

        if ( $slash )
        {
            $short = true;
        }

        $this->skipSpaces();

        if ( !$this->matchCh('>') )
        {
            $this->restoreState($restore);
            return false;
        }

        $this->skipSpaces();
        return true;
    }

    protected function tagParams( &$params = array() )
    {
        $name = null;
        $value = null;
        while ( $this->tagParam($name, $value) )
        {
            $params[$name] = $value;
            $name = '';
            $value = '';
        }
        return count($params) > 0;
    }

    protected function tagParam( &$name, &$value )
    {
        $this->saveState();
        if ( !$this->name($name, true) )
        {
            return false;
        }

        if ( !$this->matchCh('=', true) )
        {
            if ( ($this->curCh == '>' || ($this->curChClass & self::LAT) == self::LAT ) )
            {
                $value = $name;
                return true;
            }
            else
            {
                $this->restoreState();
                return false;
            }
        }

        $quote = $this->matchChClass(self::TAG_QUOTE, true);

        if ( !$this->tagParamValue($value, $quote) )
        {
            $this->restoreState();
            return false;
        }

        if ( $quote && !$this->matchCh($quote, true) )
        {
            $this->restoreState();
            return false;
        }

        $this->skipSpaces();
        return true;
    }

    protected function tagParamValue( &$value, $quote )
    {
        if ( $quote !== false )
        {
            $escape = false;
            while ( $this->curChClass && ($this->curCh != $quote || $escape) )
            {
                $escape = false;

                $value.=isset($this->entities1[$this->curCh]) ? $this->entities1[$this->curCh] : $this->curCh;

                if ( $this->curCh == '\\' )
                {
                    $escape = true;
                }
                $this->getCh();
            }
        }
        else
        {
            while ( $this->curChClass && !($this->curChClass & self::SPACE) && $this->curCh != '>' )
            {
                $value.=isset($this->entities1[$this->curCh]) ? $this->entities1[$this->curCh] : $this->curCh;
                $this->getCh();
            }
        }

        return true;
    }

    protected function tagClose( &$name )
    {
        $this->saveState();
        if ( !$this->matchCh('<') )
        {
            return false;
        }
        $this->skipSpaces();
        if ( !$this->matchCh('/') )
        {
            $this->restoreState();
            return false;
        }
        $this->skipSpaces();
        if ( !$this->name($name) )
        {
            $this->restoreState();
            return false;
        }
        $name = mb_strtolower($name, 'UTF-8');
        $this->skipSpaces();
        if ( !$this->matchCh('>') )
        {
            $this->restoreState();
            return false;
        }
        return true;
    }

    protected function makeTag( $tag, $params, $content, $short, $parentTag = null )
    {
        $this->curParentTag = $parentTag;

        $tag = mb_strtolower($tag, 'UTF-8');

        $tagRules = isset($this->tagsRules[$tag]) ? $this->tagsRules[$tag] : null;

        $parentTagIsContainer = $parentTag && isset($this->tagsRules[$parentTag][self::TR_TAG_CONTAINER]);

        if ( ( $this->blackListMode && !empty($tagRules[self::TR_TAG_LIST]) ) || (!$this->blackListMode && empty($tagRules[self::TR_TAG_LIST]) ) )
        {
            return !empty($this->tagsRules[$tag][self::TR_TAG_CUT]) ? '' : $content;
        }

        if ( (!$this->blackListMode && !$tagRules ) || (!$this->blackListMode && empty($tagRules[self::TR_TAG_LIST])) || ( $this->blackListMode && !empty($tagRules[self::TR_TAG_LIST]) ) )
        {
            return $parentTagIsContainer ? '' : $content;
        }

// hardcoded black list
        if ( in_array($tag, array('meta', 'title', 'html', 'body', 'head')) )
        {
            return '';
        }
// params processing
        $resParams = array();

        foreach ( $params as $param => $value )
        {
            $param = mb_strtolower($param, 'UTF-8');
            $value = trim($value);

// custom iframe processing
            if ( $this->mediaSrcValidate && in_array($tag, array('iframe', 'object', 'embed', 'param')) && in_array($param, array('src')) )
            {
                $val = $value;

                if ( mb_substr($val, 0, 2) == '//' )
                {
                    $val = 'http:' . $val;
                }

                $urlArr = parse_url($val);

                $parts = explode('.', $urlArr['host']);

                if ( count($parts) < 2 )
                {
                    return '';
                }

                $d1 = array_pop($parts);
                $d2 = array_pop($parts);

                $host = $d2 . '.' . $d1;

                if ( !in_array($host, $this->mediaValidSrc) && !in_array($urlArr['host'], $this->mediaValidSrc) )
                {
                    return '';
                }
            }

// if value is empty ignore param
//            if ( empty($value) )
//            {
//                continue;
//            }

            if ( $this->blackListMode )
            {
                if ( in_array('*', $this->commonTagParamRules) || !empty($tagRules[self::TR_PARAM_ALLOWED]['*']) || isset($tagRules[self::TR_PARAM_ALLOWED][$param]) || in_array($param, $this->commonTagParamRules) )
                {
                    continue;
                }
            }
            else
            {
                if ( !in_array('*', $this->commonTagParamRules) && empty($tagRules[self::TR_PARAM_ALLOWED]['*']) && !isset($tagRules[self::TR_PARAM_ALLOWED][$param]) && !in_array($param, $this->commonTagParamRules) )
                {
                    continue;
                }
            }

            $resParams[$param] = $value;
        }

        $text = '<' . $tag;

        foreach ( $resParams as $param => $value )
        {
            $text.=' ' . $param . '="' . $value . '"';
        }

        $text.= $short ? ' />' : '>';

        if ( isset($tagRules[self::TR_TAG_CONTAINER]) )
        {
            $text .= "\r\n";
        }
        if ( !$short )
        {
            $text.= $content . '</' . $tag . '>';
        }
        if ( $parentTagIsContainer )
        {
            $text .= "\r\n";
        }
        if ( $tag == 'br' )
        {
            $text.="\r\n";
        }

        return $text;
    }

    protected function comment()
    {
        return false;
        if ( !$this->matchStr('<!--') )
        {
            return false;
        }
        return $this->skipUntilStr('-->');
    }

    protected function anyThing( &$content = '', $parentTag = null )
    {
        $this->skipNL();

        while ( $this->curChClass )
        {
            $tag = '';
            $params = null;
            $text = null;
            $shortTag = false;
            $name = null;

            if ( $this->state == self::STATE_INSIDE_NOTEXT_TAG && $this->curCh != '<' )
            {
                $this->skipUntilCh('<');
            }

            if ( $this->curCh == '<' && $this->tag($tag, $params, $text, $shortTag) )
            {
                $tagText = $this->makeTag($tag, $params, $text, $shortTag, $parentTag);

                $content.=$tagText;

                if ( $tag == 'br' )
                {
                    $this->skipNL();
                }
                elseif ( empty($tagText) )
                {
                    $this->skipSpaces();
                }
            }
            elseif ( $this->curCh == '<' && $this->comment() )
            {
                continue;
            }
            elseif ( $this->curCh == '<' )
            {
                $this->saveState();
                if ( $this->tagClose($name) )
                {
                    if ( $this->state == self::STATE_INSIDE_TAG || $this->state == self::STATE_INSIDE_NOTEXT_TAG )
                    {
                        $this->restoreState();
                        return false;
                    }
                    else
                    {
                        $this->error('Unexpectedly closed tag `>` ' . $name);
                    }
                }
                else
                {
                    if ( $this->state != self::STATE_INSIDE_NOTEXT_TAG )
                    {
                        $content.= '<'; //$this->entities2['<'];
                    }
                    $this->getCh();
                }
            }
            elseif ( $this->text($text) )
            {
                $content.=$text;
            }
        }

//        $content = str_replace(array('<!--', '-->'), array('##lc##', '##lc##'), $content);
//        $content = str_replace(array('<', '>'), array('&lt;', '&gt;'), $content);
//        $content = str_replace(array('##lc##', '##lc##'), array('<!--', '-->'), $content);

        return true;
    }

    protected function skipNL( &$count = 0 )
    {
        if ( !($this->curChClass & self::NL) )
        {
            return false;
        }

        $count++;
        $firstNL = $this->curCh;
        $nl = $this->getCh();

        while ( $this->curChClass & self::NL )
        {

            if ( $nl == $firstNL )
            {
                $count++;
            }
            $nl = $this->getCh();

            $this->skipSpaces();
        }
        return true;
    }

    protected function text( &$text )
    {
        $text = '';
        $dash = '';
        $newLine = true;
        $newWord = true;
        $url = null;
        $href = null;

        while ( ($this->curCh != '<') && $this->curChClass )
        {
            $brCount = 0;
            $spCount = 0;
            $quote = null;
            $closed = false;
            $punctuation = null;
            $entity = null;

            $this->skipSpaces($spCount);

            if ( $spCount > 0 )
            {
                $text.=' ';
                $newWord = true;
            }
            elseif ( $this->skipNL($brCount) )
            {
                if ( $this->curParentTag && isset($this->tagsRules[$this->curParentTag]) && isset($this->tagsRules[$this->curParentTag][self::TR_TAG_NO_AUTO_BR]) && (is_null($this->openedTag) or isset($this->tagsRules[$this->openedTag][self::TR_TAG_NO_AUTO_BR]))
                )
                {
                    
                }
                else
                {
                    $text.= str_repeat("\r\n", $brCount);
                }

                $newLine = true;
                $newWord = true;
            }
            elseif ( $this->isAutoLinkMode && ($this->curChClass & self::LAT) && $this->openedTag != 'a' && $this->url($url, $href) )
            {
                $linkLabel = $url;

                if ( strlen($linkLabel) > 60 )
                {
                    $arr = parse_url($linkLabel);

                    $domain = (!empty($arr['scheme']) ? $arr['scheme'] . '://' : '' ) . (!empty($arr['host']) ? $arr['host'] : '' ) . (!empty($arr['port']) ? ':' . $arr['port'] : '' ) . '/';
                    $qs = (!empty($arr['path']) ? $arr['path'] : '' ) . (!empty($arr['query']) ? '?' . $arr['query'] : '' );

                    if ( strlen($qs) > 23 )
                    {
                        $qs = '...' . substr($qs, -20);
                    }

                    $linkLabel = $domain . $qs;
                }


                $text.= $this->makeTag('a', array('href' => $href, 'class' => 'ow_autolink', 'target' => '_blank', 'rel' => 'nofollow'), $linkLabel, false);
                $newWord = true;
            }
            elseif ( $this->curChClass & self::PRINTABLE )
            {
                $text.=isset($this->entities2[$this->curCh]) ? $this->entities2[$this->curCh] : $this->curCh;
                $this->getCh();
                $newWord = false;
                $newLine = false;
            }
            else
            {
                $this->getCh();
            }
        }

        $this->skipSpaces();
        return $text != '';
    }

    // fake function to optimize url function
    private function simpleMatchString( $string )
    {
        $count = mb_strlen($string);
        for ( $i = 0; $i < $count; $i++ )
        {
            if ( !isset($this->textBuf[$this->curPos + $i]) || $this->textBuf[$this->curPos + $i] !== mb_substr($string, $i, 1) )
            {
                return false;
            }
        }
        return true;
    }

    protected function url( &$url, &$href )
    {
        $hrefPrefix = null;

        if ( !empty($this->curPos) && $this->getCharClass(uniord($this->textBuf[$this->curPos - 1])) === self::PRINTABLE )
        {
            return false;
        }

        if ( $this->curCh === 'h' )
        {
            if ( $this->simpleMatchString('http://') )
            {
                $hrefPrefix = 'http://';
                $this->goToPosition($this->curPos + 7);
            }
            else if ( $this->simpleMatchString('https://') )
            {
                $hrefPrefix = 'https://';
                $this->goToPosition($this->curPos + 8);
            }
        }
        else if ( $this->curCh === 'w' )
        {
            if ( $this->simpleMatchString('www.') )
            {
                $hrefPrefix = 'http://www.';
                $urlPrefix = 'www.';
                $this->goToPosition($this->curPos + 4);
            }
        }

        if ( $hrefPrefix === null )
        {
            return false;
        }

        $this->saveState();
        $url = '';
        $urlChMask = self::URL | self::ALPHA;
        $urlPunctValidChars = array(":", ",", "!", "'", "~", ".", ";");
        
        while ( $this->curChClass & $urlChMask || in_array($this->curCh, $urlPunctValidChars) )
        {            
            $url.= $this->curCh;
            $this->getCh();
        }
        
        $chCount = 0;        
        
        for( $i = (mb_strlen($url) - 1); $i >= 0; $i-- )
        {
            if( in_array(mb_substr($url, $i, 1), $urlPunctValidChars) )
            {
                $chCount++;
            }
            else
            {
                break;
            }
        }
        
        if( $chCount > 0 )
        {
            $url = mb_substr($url, 0, mb_strlen($url) - $chCount);
            $this->goToPosition($this->curPos-$chCount);
        }       
        
        if( mb_strlen($url) == 0 )
        {
            return false;
        }
        
        if ( !mb_strlen($url) )
        {
            $this->restoreState();
            return false;
        }

        $href = $hrefPrefix . $url;
        $url = !empty($urlPrefix) ? $urlPrefix . $url : $href;
        return true;
    }

    protected function error( $message )
    {
        $str = '';
        $strEnd = min($this->curPos + 8, $this->textLen);
        for ( $i = $this->curPos; $i < $strEnd; $i++ )
        {
            $str.=$this->textBuf[$i];
        }

        $this->errors[] = array(
            'message' => $message,
            'pos' => $this->curPos,
            'ch' => $this->curCh,
            'line' => 0,
            'str' => $str,
        );
    }
}

function uniord( $c )
{
    $h = ord($c{0});
    if ( $h <= 0x7F )
    {
        return $h;
    }
    else if ( $h < 0xC2 )
    {
        return false;
    }
    else if ( $h <= 0xDF )
    {
        return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
    }
    else if ( $h <= 0xEF )
    {
        return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6 | (ord($c{2}) & 0x3F);
    }
    else if ( $h <= 0xF4 )
    {
        return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12 | (ord($c{2}) & 0x3F) << 6 | (ord($c{3}) & 0x3F);
    }
    else
    {
        return false;
    }
}
