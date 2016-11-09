<?php
namespace apanly\metaweblog;
/**
 MetaWeblog API中文说明
1、什么是MetaWeblog API？
MetaWeblog API（MWA）是一个Blog程序接口标准，允许外部程序来获取或者设置Blog的文字和熟悉。他建立在XMLRPC接口之上，并且已经有了很多的实现。
2、基本的函数规范
metaWeblog.newPost (blogid, username, password, struct, publish) 返回一个字符串，可能是Blog的ID。
metaWeblog.editPost (postid, username, password, struct, publish) 返回一个Boolean值，代表是否修改成功。
其中blogid、username、password分别代表Blog的id（注释：如果你有两个Blog，blogid指定你需要编辑的blog）、用户名和密码。
 *
 * 综上 本库只是先了newPost 和 editPost 其他的未实现，也不打算实现了
**/
class MetaWeblog {
    private  $xml = '';
    private  $method = "";//默认发布新文章
    private  $url = "";
    private  $charset = "utf-8";
    private  $username = "";
    private  $passwd = "";
    private  $blog_id = "1";
    private  $metaweblog_response = null;
    private  $error = null;
    private  $header = [
        "Accept" => "*/*",
        "Accept-Language" => 'zh-CN, en-US, en, *',
        'Content-Type'  => 'text/xml;charset=utf-8',
        'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Windows Live Writer 1.0)',
    ];


	/**
	 * MetaWeblog constructor.
	 * @param $url api地址
	 * @param string $charset
	 */
    public function __construct( $url,$charset = "utf-8" ){
        $this->url = $url;
        $this->charset = $charset;
    }

	/**
	 * 设置认证信息
	 * @param $username 用户名
	 * @param $passwd 密码
	 */
    public function setAuth($username,$passwd){
        $this->username = $username;
        $this->passwd = $passwd;
    }

	/**
	 * 发表新文章
	 * @param  $params 数组
	 * [
	 * 		'title' => '文章标题',
	 * 		'description' => '文章内容',
	 * 		'categories' => '类别，是一个数组'
	 * ]
	 * @param  $is_csdn 是不是发到csdn网站
	 */
    public function newPost( $params,$is_csdn = false ){
        $this->method = "metaWeblog.newPost";
        if( $is_csdn ){//csdn要特殊处理下
        	$this->blog_id = 895030;
		}
        $this->buildXML( $params );
        $res_xml = $this->doPost();;
        $this->metaweblog_response = new MetaWeblog_Message( $res_xml );
        if( !$this->metaweblog_response->parse() ){
            $this->error = new MetaWeblog_Error(-32700, 'parse error. not well formed');
            return false;
        }

        if ($this->metaweblog_response->messageType == 'fault') {
            $this->error = new MetaWeblog_Error($this->metaweblog_response->faultCode, $this->metaweblog_response->faultString);
            return false;
        }

        $this->setBlogId( $this->metaweblog_response->params[0] );

        return true;
    }

	/**
	 * 编辑文章
	 * @param $blog_id
	 * @param  $params 数组
	 * [
	 * 		'title' => '文章标题',
	 * 		'description' => '文章内容',
	 * 		'categories' => '类别，是一个数组'
	 * ]
	 */
    public function editPost( $blog_id,$params ){
        $this->blog_id = $blog_id;
        $this->method = "metaWeblog.editPost";
        $this->buildXML( $params );
        $res_xml = $this->doPost();;
        $this->metaweblog_response = new MetaWeblog_Message( $res_xml );
        if( !$this->metaweblog_response->parse() ){
            $this->error = new MetaWeblog_Error(-32700, 'parse error. not well formed');
            return false;
        }

        if ($this->metaweblog_response->messageType == 'fault') {
            $this->error = new MetaWeblog_Error($this->metaweblog_response->faultCode, $this->metaweblog_response->faultString);
            return false;
        }

        return true;
    }

    private function doPost(){
        $this->header['Content-Type'] = 'text/xml;charset='.$this->charset;
        $header = [];
        foreach( $this->header as $_h_key => $_h_val ){
            $header[] = "{$_h_key}: {$_h_val}";
        }

        $ch = curl_init ( $this->url );
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_VERBOSE, true);//命令行显示
        curl_setopt($ch, CURLOPT_ENCODING, $this->charset);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml );
        $response = curl_exec($ch);
        if( curl_errno($ch) ){
            echo curl_error($ch);
            return false;
        }
        curl_close($ch);
        return $response;
    }

    public function getBlogId(){
		return $this->blog_id;
	}

	private function setBlogId( $blog_id ){
		$this->blog_id = $blog_id;
	}

    public function getResponse(){
    	if( $this->metaweblog_response ){
			return $this->metaweblog_response->getMessage();
		}
		return false;
    }

	public function isError(){
        return (is_object($this->error));
    }

	public function getErrorCode(){
        return $this->error->code;
    }

	public function getErrorMessage(){
        return $this->error->message;
    }

    private function buildXML($params ){
        $this->xml = <<<EOD
<?xml version="1.0" encoding="{$this->charset}"?>
<methodCall>
<methodName>{$this->method}</methodName>
<params>
    <param>
        <value>
        <string>{$this->blog_id}</string>
        </value>
    </param>
    <param>
        <value>
        <string>{$this->username}</string>
        </value>
    </param>
    <param>
        <value>
        <string>{$this->passwd}</string>
        </value>
    </param>
EOD;
        $this->xml .= $this->buildBody( $params );
        $this->xml .= <<<EOT

    <param>
       <value>
        <boolean>1</boolean>
       </value>
    </param>
EOT;

        $this->xml .= <<<EOT

</params></methodCall>
EOT;
    }

    private function buildBody( $params ){
        $xml = <<<EOD

    <param>
       <value>
        <struct>
EOD;
        foreach( $params as $_key => $_val ){
            if( is_array( $_val ) ){
                $xml .= $this->buildMainArr( $_key,$_val );
            }else{
                $xml .= <<<EOD

            <member>
              <name>{$_key}</name>
              <value>
                <string>{$_val}</string>
              </value>
            </member>
EOD;
            }


        }

        $xml .= <<<EOD

        </struct>
       </value>
    </param>
EOD;

    return  $xml;
    }

    private function buildMainArr($name,$data){
        $xml = <<<EOD

            <member>
                <name>{$name}</name>
                <value>
                  <array>
                    <data>
EOD;
        foreach( $data as $_val ){
            $xml .= <<<EOD

                        <value>
                            <string>{$_val}</string>
                        </value>
EOD;
        }

        $xml .= <<<EOD

                     </data>
                   </array>
                  </value>
            </member>
EOD;
        return $xml;


    }
}


class  MetaWeblog_Message{

    var $message;
    var $messageType;  // methodCall / methodResponse / fault
    var $faultCode;
    var $faultString;
    var $methodName;
    var $params;

    // Current variable stacks
    var $_arraystructs = array();   // The stack used to keep track of the current array/struct
    var $_arraystructstypes = array(); // Stack keeping track of if things are structs or array
    var $_currentStructName = array();  // A stack as well
    var $_param;
    var $_value;
    var $_currentTag;
    var $_currentTagContents;
    // The XML parser
    var $_parser;

    public function __construct($message){
        $this->message = $message;
    }

    function parse(){
        $header = preg_replace( '/<\?xml.*?\?'.'>/', '', substr($this->message, 0, 100), 1);
        $this->message = substr_replace($this->message, $header, 0, 100);
        if (trim($this->message) == '') {
            return false;
        }
        $this->_parser = xml_parser_create();
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($this->_parser, 'cdata');
        $chunk_size = 262144; // 256Kb, parse in chunks to avoid the RAM usage on very large messages
        $final = false;
        do {
            if (strlen($this->message) <= $chunk_size) {
                $final = true;
            }
            $part = substr($this->message, 0, $chunk_size);
            $this->message = substr($this->message, $chunk_size);
            if (!xml_parse($this->_parser, $part, $final)) {
                return false;
            }
            if ($final) {
                break;
            }
        } while (true);
        xml_parser_free($this->_parser);
        if ($this->messageType == 'fault') {
            $this->faultCode = $this->params[0]['faultCode'];
            $this->faultString = $this->params[0]['faultString'];
        }
        return true;
    }

    function tag_open($parser, $tag, $attr){
        $this->_currentTagContents = '';
        $this->currentTag = $tag;
        switch($tag) {
            case 'methodCall':
            case 'methodResponse':
            case 'fault':
                $this->messageType = $tag;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':    // data is to all intents and puposes more interesting than array
                $this->_arraystructstypes[] = 'array';
                $this->_arraystructs[] = array();
                break;
            case 'struct':
                $this->_arraystructstypes[] = 'struct';
                $this->_arraystructs[] = array();
                break;
        }
    }

    function cdata($parser, $cdata){
        $this->_currentTagContents .= $cdata;
    }

    function tag_close($parser, $tag){
        $valueFlag = false;
        switch($tag) {
            case 'int':
            case 'i4':
                $value = (int)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'double':
                $value = (double)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'string':
                $value = (string)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
//            case 'dateTime.iso8601':
//                $value = new IXR_Date(trim($this->_currentTagContents));
//                $valueFlag = true;
//                break;
            case 'value':
                // "If no type is indicated, the type is string."
                if (trim($this->_currentTagContents) != '') {
                    $value = (string)$this->_currentTagContents;
                    $valueFlag = true;
                }
                break;
            case 'boolean':
                $value = (boolean)trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'base64':
                $value = base64_decode($this->_currentTagContents);
                $valueFlag = true;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':
            case 'struct':
                $value = array_pop($this->_arraystructs);
                array_pop($this->_arraystructstypes);
                $valueFlag = true;
                break;
            case 'member':
                array_pop($this->_currentStructName);
                break;
            case 'name':
                $this->_currentStructName[] = trim($this->_currentTagContents);
                break;
            case 'methodName':
                $this->methodName = trim($this->_currentTagContents);
                break;
        }

        if ($valueFlag) {
            if (count($this->_arraystructs) > 0) {
                // Add value to struct or array
                if ($this->_arraystructstypes[count($this->_arraystructstypes)-1] == 'struct') {
                    // Add to struct
                    $this->_arraystructs[count($this->_arraystructs)-1][$this->_currentStructName[count($this->_currentStructName)-1]] = $value;
                } else {
                    // Add to array
                    $this->_arraystructs[count($this->_arraystructs)-1][] = $value;
                }
            } else {
                // Just add as a paramater
                $this->params[] = $value;
            }
        }
        $this->_currentTagContents = '';
    }

    public function getMessage(){
    	return $this->message;
	}
}


class MetaWeblog_Error{
    var $code;
    var $message;

    public function __construct($code, $message){
        $this->code = $code;
        $this->message = htmlspecialchars($message);
    }

    function getXml(){
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$this->code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$this->message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
        return $xml;
    }
}