<?php

/*
   ____  __      __    ___  _  _  ___    __   ____     ___  __  __  ___
  (  _ \(  )    /__\  / __)( )/ )/ __)  /__\ (_  _)   / __)(  \/  )/ __)
   ) _ < )(__  /(__)\( (__  )  (( (__  /(__)\  )(    ( (__  )    ( \__ \
  (____/(____)(__)(__)\___)(_)\_)\___)(__)(__)(__)    \___)(_/\/\_)(___/

	@author			Black Cat Development
	@copyright		2016 Black Cat Development
	@link			http://blackcat-cms.org
	@license		http://www.gnu.org/licenses/gpl.html
	@category		CAT_Core
	@package		CAT_Core

*/

require_once "class.blackNewsEntry.php";

if (!class_exists("blackNews", false)) {
    if (!class_exists("CAT_Addon_Page", false)) {
        include dirname(__FILE__) . "/../Engine/CAT_Addons/CAT_Addon_Page.php";
    }

    class blackNews extends CAT_Addon_Page
    {
        /**
         * @var void
         */
        protected static $instance = null;

        protected static $template = "modify";
        private static $SEOUrl;
        private static $categories = [];
        protected static $options = [];
        private static $routeUrl = null;
        private static $routeQuery = null;
        protected static $imageDir = "/media/blacknews/";
        protected static $imageName = "bNimage";
        private static $parsed = null;

        /**
         * @var void
         */
        protected static $name = "blackNews";
        protected static $directory = "blacknews";
        protected static $version = "0.7";
        protected static $author = "Matthias Glienke, creativecat";
        protected static $license = "GNU General Public License";
        protected static $description = "Module for implementing news";
        protected static $guid = "3cf5feb8-7873-4d55-a6f4-33aafed211da";
        protected static $home = "https://creativecat.de";
        protected static $platform = "2.x";

        public function __construct()
        {
            parent::__construct();
        }
        public function __destruct()
        {
            parent::__destruct();
        }

        public static function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         *
         */
        public static function getVariant(bool $fromDB = false)
        {
            // set and get variant of module
            static::$variant = self::getOption("variant", $fromDB);
            return static::$variant;
        }

        /**
         * @param void $name
         * @param void $value
         */
        private static function saveOption()
        {
            if ($options = CAT_Helper_Validate::sanitizePost("options")) {
                foreach ($options as $opt) {
                    self::setOption($opt["name"], $opt["value"]);
                }
            }
            return [
                "message" => "Eintrag gespeichert",
                "success" => true,
            ];
        } // end saveOption()

        /**
         * @param void $name
         * @param void $value
         */
        private static function setOption($name, $value)
        {
            self::setIDs();

            if ($name == "permalink") {
                $value = trim($value, "/");
                #$oldDir	= self::getOption('permalink');

                // ToDo!s:
                // * check if directory already exists
                // * .htaccess anpassen
            }

            // Set info into table
            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "INSERT INTO `:prefix:mod_blackNewsOptions` " .
                            "(`section_id`, `name`, `value`) VALUES ( :section_id, :name, :value ) " .
                            "ON DUPLICATE KEY UPDATE `value` = :value",
                        [
                            "section_id" => self::$section_id,
                            "name" => $name,
                            "value" => $value,
                        ]
                    )
            ) {
                return true;
            } else {
                return false;
            }
        } // end setOption()

        /**
         *
         */
        public static function getOption($name = null, $fromDB = false)
        {
            if ($name && !$fromDB && isset(self::$options[$name])) {
                return self::$options[$name];
            }

            self::setIDs();

            // Get all options
            $result = self::getInstance()
                ->db()
                ->query(
                    "SELECT `value`, `name` FROM `:prefix:mod_blackNewsOptions` " .
                        "WHERE `section_id` = :section_id",
                    [
                        "section_id" => self::$section_id,
                    ]
                );
            if ($result && $result->rowCount() > 0) {
                while (false !== ($option = $result->fetch())) {
                    self::$options[$option["name"]] = $option["value"];
                }
            }
            if ($name) {
                return self::$options[$name] ?? null;
            }
            return self::$options;
        }

        /**
         *
         */
        public static function getAllNewsSections(): array
        {
            // Get all options
            $result = self::getInstance()
                ->db()
                ->query(
                    "SELECT s.`section_id`, s.`name`, p.`menu_title`, p.`link`, p.`page_id` FROM `:prefix:sections` s " .
                        "JOIN `:prefix:pages` p " .
                        "ON s.`page_id` = p.`page_id` " .
                        "WHERE s.`module` = :module AND " .
                        " s.`section_id` <> :secID",
                    [
                        "module" => static::$directory,
                        "secID" => self::$section_id ? self::$section_id : 0,
                    ]
                );
            $sections = [];
            if ($result && $result->rowCount() > 0) {
                while (false !== ($sec = $result->fetch())) {
                    $sections[$sec["section_id"]] = [
                        "name" => $sec["name"],
                        "menu_title" => $sec["menu_title"],
                        "page_id" => $sec["page_id"],
                        "link" => $sec["link"],
                    ];
                }
            }
            return $sections;
        }

        /**
         *
         */
        protected function getTemplate()
        {
            // TODO: implement here
            return "view";
        }

        /**
         *
         */
        protected function setTemplate()
        {
            // TODO: implement here
        }

        /**
         *
         */
        /*	public static function getID()
		{
			self::setIDs();
			// Add a new blackNews
			self::$bnID	= self::getInstance()->db()->query(
				'SELECT `bnID` ' .
					'FROM `:prefix:mod_blackNews` ' .
						'WHERE `section_id` = :section_id ',
				array(
					'section_id'	=> self::$section_id
				)
			)->fetchColumn();

			return self::$bnID;
		}*/

        /**
         *
         */
        public static function getOverview(
            bool $addOpt = false,
            int $start = 0,
            int $limit = 0
        ) {
            $entries = [];
            $catID = self::getOption("category")
                ? self::getOption("category")
                : 0;
            $secID = self::getOption("setNews")
                ? self::getOption("setNews")
                : 0;

            $between = $limit > 0 ? " LIMIT " . $start . "," . $limit : "";

            // Get all entries
            if ($catID > 0) {
                $result = self::getInstance()
                    ->db()
                    ->query(
                        "SELECT nE.*, cat.*, " .
                            'DATE_FORMAT(nE.`publishDate`, "%Y-%m-%d") AS publishDate, ' .
                            'DATE_FORMAT(nE.`publishDate`, "%H:%i") AS publishTime, ' .
                            'DATE_FORMAT(nE.`unpublishDate`, "%Y-%m-%d") AS unpublishDate, ' .
                            'DATE_FORMAT(nE.`unpublishDate`, "%H:%i") AS unpublishTime, ' .
                            "UNIX_TIMESTAMP(nE.`publishDate`) AS publishUT " .
                            "FROM `:prefix:mod_blackNewsEntry` nE " .
                            "LEFT JOIN `:prefix:mod_blackNewsCategoryEntries` catE " .
                            "ON catE.`entryID` = nE.`entryID` " .
                            "LEFT JOIN `:prefix:mod_blackNewsCategory` cat " .
                            "ON cat.`catID` = catE.`catID` " .
                            "WHERE catE.`catID` = :catID " .
                            "AND nE.`section_id` = :section_id " .
                            "ORDER BY nE.`position` DESC, nE.`publishDate` DESC" .
                            $between,
                        [
                            "section_id" => $secID ? $secID : self::$section_id,
                            "catID" => $catID,
                        ]
                    );
            } else {
                $result = self::getInstance()
                    ->db()
                    ->query(
                        "SELECT nE.*, C.*, " .
                            'DATE_FORMAT(nE.`publishDate`, "%Y-%m-%d") AS publishDate, ' .
                            'DATE_FORMAT(nE.`publishDate`, "%H:%i") AS publishTime, ' .
                            'DATE_FORMAT(nE.`unpublishDate`, "%Y-%m-%d") AS unpublishDate, ' .
                            'DATE_FORMAT(nE.`unpublishDate`, "%H:%i") AS unpublishTime, ' .
                            "UNIX_TIMESTAMP(nE.`publishDate`) AS publishUT " .
                            "FROM `:prefix:mod_blackNewsEntry` nE " .
                            "LEFT JOIN `:prefix:mod_blackNewsCategoryEntries` catE ON catE.`entryID` = nE.`entryID`" .
                            "LEFT JOIN `:prefix:mod_blackNewsCategory` C ON catE.catID = C.catID " .
                            "WHERE nE.`section_id` = :section_id " .
                            "ORDER BY nE.`position` DESC, nE.`publishDate` DESC" .
                            $between,
                        [
                            "section_id" => $secID ? $secID : self::$section_id,
                        ]
                    );
            }

            if ($result && $result->rowCount() > 0) {
                $i = 0;
                while (false !== ($entry = $result->fetch())) {
                    $entries[++$i] = $entry;
                    if ($addOpt) {
                        foreach (
                            blackNewsEntry::getInstance(
                                $entry["entryID"]
                            )->getOption()
                            as $name => $value
                        ) {
                            $entries[$i][$name] = $value;
                        }
                        $entries[$i][
                            "username"
                        ] = CAT_Users::getInstance()->get_user_details(
                            $entries[$i]["userID"],
                            "username"
                        );
                        $entries[$i][
                            "display_name"
                        ] = CAT_Users::getInstance()->get_user_details(
                            $entries[$i]["userID"],
                            "display_name"
                        );
                        $entries[$i]["image"] = blackNewsEntry::getInstance(
                            $entry["entryID"]
                        )->getImage();
                    }
                }
            }
            return $entries;
        }

        /**
         *
         */
        /*public static function getByCategory($addOpt=NULL,int $catID=0)
		{
			// Get all entries
			$result = self::getInstance()->db()->query(
				'SELECT * FROM `:prefix:mod_blackNewsEntry` E ' .
					'INNER JOIN `:prefix:mod_blackNewsCategoryEntries` EC ON E.entryID = EC.entryID ' .
					'INNER JOIN `:prefix:mod_blackNewsCategory` C ON EC.catID = C.catID ' .
					'WHERE C.catID = :catID ' .
						'AND E.`section_id` = :section_id ' .
						'ORDER BY E.`position` DESC',
				array(
					'section_id'		=> self::$section_id,
					'catID'				=> $catID
				)
			);
			if( $result && $result->rowCount() > 0 )
			{
				$i	= 0;
				while ( false !== ( $entry = $result->fetch() ) )
				{
					$entries[++$i]	= $entry;
					if($addOpt)
					{
						foreach( blackNewsEntry::getInstance($entry['entryID'])->getOption() as $name => $value )
						{
							$entries[$i][$name]	= $value;
						}
						$entries[$i]['username']	= CAT_Users::getInstance()->get_user_details($entries[$i]['userID'],'username');
						$entries[$i]['display_name']	= CAT_Users::getInstance()->get_user_details($entries[$i]['userID'],'display_name');
						$entries[$i]['image']			= blackNewsEntry::getInstance($entry['entryID'])->getImage();
					}
				}
			}
			return $entries;
		}*/

        /**
         *
         */
        private static function getReferer(): int
        {
            if ($_SERVER["HTTP_REFERER"] == "") {
                return 0;
            }

            $route = array_filter(
                explode("/", parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH))
            );
            $urls = [];

            foreach ($route as $k => $r) {
                $urls[$k] =
                    (count($urls) > 0 ? $urls[$k - 1] : "") .
                    "/" .
                    trim($r, ".php");
            }

            // Wenn Referrer in Kategorien vorkommt, dann leite Zurückbutton auf Kategorie zurück
            self::getCategories();
            foreach (self::$categories as $ind) {
                if ($ind["url"] == end($route)) {
                    return $ind["catID"];
                }
            }
            // Wenn Referer als Page-URL vorkommt, dann leite Zurückbutton auf Seite zurück
            // Negativer Wert der Page-ID um zu catID unterscheiden zu können.
            $secs = self::getAllNewsSections();
            foreach ($secs as $k => $v) {
                if (in_array($v["link"], $urls)) {
                    return -(int) $v["page_id"];
                }
            }
            return 0;
        }

        /**
         *
         */
        private static function getEntry()
        {
            // TODO: Check if route is in database, else return 404
            // TODO: Add route to extra table with trigger to get history of files and automatically set 301

            return blackNewsEntry::getEntryByURL(
                trim(
                    str_replace(
                        self::getOption("permalink"),
                        "",
                        self::$routeUrl
                    ),
                    "/"
                )
            );
        }

        /**
         *
         */
        public static function getCategories(int $section_id = 0)
        {
            if (count(self::$categories) > 0) {
                return self::$categories;
            }

            // Get all categories
            $r = self::getInstance()
                ->db()
                ->query(
                    "SELECT * " .
                        "FROM `:prefix:mod_blackNewsCategory` " .
                        "WHERE `section_id` = :section_id",
                    [
                        "section_id" =>
                            $section_id > 0 ? $section_id : self::$section_id,
                    ]
                );
            if ($r && $r->rowCount() > 0) {
                while (false !== ($c = $r->fetch())) {
                    self::$categories[$c["catID"]] = $c;
                }
            }
            return self::$categories;
        }

        /**
         * @param void $dir
         */
        private static function createPermalink($dir = "")
        {
            if (
                trim($dir, "/") != "" &&
                CAT_Helper_Directory::createDirectory(
                    CAT_PATH . "/" . trim($dir, "/"),
                    null,
                    false
                )
            ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * @param void $old
         * @param void $new
         */
        private static function renamePermalink($old = null, $new = null)
        {
            if (!$old || !$new) {
                return false;
            }
            if (trim($new, "/") == trim($old, "/")) {
                return true;
            }

            $nDir = CAT_PATH . "/" . trim($new, "/");
            $oDir = CAT_PATH . "/" . trim($old, "/");

            if ((file_exists($nDir) && is_dir($nDir)) || !is_dir($oDir)) {
                return false;
            }

            if (rename($oDir, $nDir)) {
                self::setOption("permalink", $new);
                return true;
            } else {
                return false;
            }
        }

        /**
         * get all offers from database
         *
         * @access public
         * @param  string/array  $id - id/ids of offer
         * @param  string  $output - if table to print - default false
         * @return array()
         *
         **/
        public static function createTitleURL($title = null)
        {
            if (!$title) {
                return false;
            }

            return CAT_Helper_Page::getFilename($title);
        } // end createTitleURL()

        /**
         *
         * @access public
         * @param  array  $arr - array( date (dd.mm.yyyy), start (hh:mm), end (hh:mm) )
         * @return
         **/
        public function toTimestamp($arr = null)
        {
            if (!$arr || !is_array($arr)) {
                return false;
            }

            $ts = [];
            foreach ($arr as $date) {
                if (strpos($date["date"], "-") !== false) {
                    list($y, $m, $d) = explode("-", $date["date"]);
                } else {
                    list($d, $m, $y) = explode(".", $date["date"]);
                }
                list($sh, $sm) = explode(":", $date["time_start"]);
                list($eh, $em) = explode(":", $date["time_end"]);
                $ts[] = [
                    mktime($sh, $sm, 0, $m, $d, $y),
                    mktime($eh, $em, 0, $m, $d, $y),
                ];
            }
            return $ts;
        } // end function toTimestamp()

        /**
         *
         */
        public static function add()
        {
            // Add a new blackNews
            return true;
            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "INSERT INTO `:prefix:mod_blackNews` " .
                            "( `section_id` ) VALUES " .
                            "( :section_id )",
                        [
                            "section_id" => self::$section_id,
                        ]
                    )
            ) {
                #self::$bnID	= self::getInstance()->db()->lastInsertId();
                if (
                    self::getInstance()
                        ->db()
                        ->query(
                            "INSERT INTO `:prefix:mod_blackNewsOptions` " .
                                "( `section_id`, `name`, `value` ) VALUES " .
                                '( :section_id, "variant", "default" )',
                            [
                                "section_id" => self::$section_id,
                            ]
                        )
                ) {
                    return $this;
                }
            } else {
                return null;
            }
        }

        /**
         *
         */
        public static function update()
        {
            // Currently empty - placeholder for future updates
        }

        /**
         *
         */
        public static function checkRedirect()
        {
            $rUrl = CAT_Registry::get("USE_SHORT_URLS")
                ? CAT_URL . "/" . self::$routeUrl
                : CAT_URL .
                    "/" .
                    trim(PAGES_DIRECTORY, "/") .
                    "/" .
                    self::$routeUrl;
            $sUrl = CAT_Helper_Page::getLink(self::$page_id);

            if (self::getOption("permalink") . ".php" == self::$routeUrl) {
                $redirect = CAT_Registry::get("USE_SHORT_URLS")
                    ? CAT_URL . "/" . self::getOption("permalink")
                    : CAT_URL .
                        "/" .
                        trim(PAGES_DIRECTORY, "/") .
                        "/" .
                        self::getOption("permalink");
                header("HTTP/1.1 301 Moved Permanently");
                header("Location:" . $redirect);
                exit();
            }

            /*			if( $rUrl == $sUrl && ( $rUrl == CAT_URL . $_SERVER['REQUEST_URI'] ) )
			{
				$redirect	= CAT_Registry::get('USE_SHORT_URLS') ?
					CAT_URL  . '/' . self::getOption('permalink') . '/'
					: CAT_URL . '/' . trim(PAGES_DIRECTORY,'/') . '/' . self::getOption('permalink') . '/';
				header("HTTP/1.1 301 Moved Permanently");
				header("Location:" . $redirect );
				exit();
			}*/
        }

        /**
         *
         */
        public static function checkOverview(string $rUrl = ""): bool
        {
            if (
                self::$routeUrl == self::getOption("permalink") ||
                $rUrl == CAT_Helper_Page::getLink(self::$page_id) ||
                $rUrl . ".php" == CAT_Helper_Page::getLink(self::$page_id) ||
                trim($rUrl, "/") ==
                    trim(CAT_Helper_Page::getLink(self::$page_id), "/") ||
                trim($rUrl, "/") == trim(CAT_URL, "/")
            ) {
                return true;
            } else {
                return false;
            }
        }

        /**
         *
         */
        public static function checkCategory(): bool
        {
            if (self::$routeUrl == "") {
                return false;
            }
            $route = explode("/", self::$routeUrl);

            self::getCategories();
            foreach (self::$categories as $ind) {
                if ($ind["url"] == end($route)) {
                    self::$options["category"] = $ind["catID"];
                    return true;
                }
            }
            return false;
        }

        /**
         *
         */
        public static function view(bool $output = true)
        {
            global $parser, $section_id;

            self::setIDs($section_id);

            // Reset parserValues
            self::$parserValues = [];

            self::getRoute(PAGES_DIRECTORY);

            self::checkRedirect();

            $rUrl = CAT_Registry::get("USE_SHORT_URLS")
                ? CAT_URL . "/" . self::$routeUrl
                : CAT_URL .
                    "/" .
                    trim(PAGES_DIRECTORY, "/") .
                    "/" .
                    self::$routeUrl;

            self::setParserValue("options", self::getOption(), true);
            #echo '<pre style="font-size:10px;line-height:12px">';
            #print_r(self::getParserValue());
            #echo '</pre>';
            self::setParserValue("categories", self::getCategories(), true);

            // Check if user came from CategoryPage
            self::setParserValue("referer", self::getReferer(), true);

            if (self::checkOverview($rUrl) || self::checkCategory()) {
                self::setParserValue(
                    "category",
                    self::getOption("category"),
                    true
                );
                self::setParserValue("entries", self::getOverview(true), true);
                static::$template = "view";
            } else {
                self::setParserValue("entry", self::getEntry());
                static::$template = "viewEntry";
            }

            if (
                file_exists(
                    CAT_PATH .
                        "/modules/" .
                        static::$directory .
                        "/view/" .
                        self::getVariant() .
                        "/view.php"
                )
            ) {
                include CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/view/" .
                    self::getVariant() .
                    "/view.php";
            }
            if ($output) {
                $parser->setPath(
                    CAT_PATH .
                        "/modules/" .
                        static::$directory .
                        "/templates/" .
                        self::getVariant()
                );
                $parser->setFallbackPath(
                    CAT_PATH .
                        "/modules/" .
                        static::$directory .
                        "/templates/default"
                );

                if (!self::$parsed) {
                    $parser->output(static::$template, self::getParserValue());
                }
            } else {
                return self::getParserValue();
            }
            #self::$parsed = true;
        }

        /**
         *
         */
        public static function includeNews(
            ?int $section_id = null,
            string $template = "",
            bool $out = true,
            int $start = 0,
            int $limit = 0
        ) {
            global $parser;
            $options = self::getOption();

            self::setIDs($section_id);
            self::getVariant();

            /*			self::getRoute(PAGES_DIRECTORY);

			self::checkRedirect();

			$rUrl	= CAT_Registry::get('USE_SHORT_URLS') ?
					CAT_URL . '/' . self::$routeUrl
					: CAT_URL . '/' . trim(PAGES_DIRECTORY,'/') . '/' . self::$routeUrl;

			if ( self::$routeUrl == self::getOption('permalink') )
				#|| !self::$routeQuery )#$rUrl == CAT_Helper_Page::getLink(self::$page_id) )
			{
				self::setParserValue('entries',self::getOverview(true));
				static::$template	= 'view';
			} else {
				self::setParserValue('entry',self::getEntry());
				static::$template	= 'viewEntry';
			}
*/
            self::setParserValue("categories", self::getCategories());

            if ($options["category"]) {
                self::setParserValue(
                    "entries",
                    self::getOverview(true, $options["category"])
                );
            } else {
                self::setParserValue(
                    "entries",
                    self::getOverview(true, $start, $limit)
                );
            }

            static::$template = $template != "" ? $template : "view";

            self::setParserValue("options", self::getOption());

            // Muss ich noch checken... weiß ich selbst gerade nicht mehr, warum das gesetzt sein muss
            self::$options["variant"] = $options["variant"];

            $parser->setPath(
                CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/templates/" .
                    self::getVariant()
            );
            $parser->setFallbackPath(
                CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/templates/default"
            );

            if (!$out) {
                return $parser->get(static::$template, self::getParserValue());
            }

            if (!self::$parsed && $out) {
                $parser->output(static::$template, self::getParserValue());
            }

            #self::$parsed = true;
        }

        /**
         * retrieve the route
         *
         * @access private
         * @return
         **/
        private static function getRoute($remove_prefix = null)
        {
            foreach (
                array_values([
                    "REQUEST_URI",
                    "REDIRECT_SCRIPT_URL",
                    "SCRIPT_URL",
                    "ORIG_PATH_INFO",
                    "PATH_INFO",
                ])
                as $key
            ) {
                if (isset($_SERVER[$key])) {
                    self::$routeUrl = parse_url($_SERVER[$key], PHP_URL_PATH);

                    self::$routeQuery = parse_url(
                        $_SERVER[$key],
                        PHP_URL_QUERY
                    );

                    if (
                        strpos($_SERVER[$key], "?") &&
                        strpos($_SERVER[$key], "=")
                    ) {
                        $tmp = explode("?", $_SERVER[$key]);
                        self::$routeUrl = $tmp[0];
                    }
                    self::$routeQuery = "";
                    if (self::$routeQuery > "") {
                        self::$routeUrl =
                            self::getOption("permalink") .
                            "/" .
                            str_replace("q=", "", self::$routeQuery);
                    }
                    break;
                }
            }
            if (!self::$routeUrl) {
                self::$routeUrl = "/";
            }

            // remove params
            if (stripos(self::$routeUrl, "?")) {
                list(self::$routeUrl, $ignore) = explode(
                    "?",
                    self::$routeUrl,
                    2
                );
            }

            $path_prefix = str_ireplace(
                CAT_Helper_Directory::sanitizePath($_SERVER["DOCUMENT_ROOT"]),
                "",
                CAT_Helper_Directory::sanitizePath(CAT_PATH)
            );

            // if there's a prefix to remove
            if ($remove_prefix) {
                self::$routeUrl = str_replace(
                    trim($remove_prefix, "/"),
                    "",
                    self::$routeUrl
                );
            }

            // Remove leading and ending "/" and multiple slashes
            self::$routeUrl = trim(
                CAT_Helper_Directory::sanitizePath(self::$routeUrl),
                "/"
            );

            return [self::$routeUrl, self::$routeQuery];
        } // end function initRoute()

        /**
         *
         */
        protected static function order($entryIDs = [])
        {
            $counter = count($entryIDs);
            if ($counter > 0) {
                foreach ($entryIDs as $entry) {
                    self::getInstance()
                        ->db()
                        ->query(
                            "UPDATE `:prefix:mod_blackNewsEntry` " .
                                "SET `position` = :position " .
                                "WHERE `entryID` = :entryID",
                            [
                                "entryID" => $entry,
                                "position" => $counter--,
                            ]
                        );
                }
            }
            return true;
        }

        /**
         *
         */
        public static function modify()
        {
            global $parser, $section_id;

            self::setIDs($section_id);

            // Reset parserValues
            self::$parserValues = [];

            self::setParserValue("options", self::getOption(), true);
            self::setParserValue("sections", self::getAllNewsSections(), true);

            self::setParserValue("entries", self::getOverview(), true);
            self::setParserValue("categories", self::getCategories(), true);

            $setNews = (int) (self::$parserValues["options"]["setNews"] ?? 0);

            if ($setNews && $setNews !== self::$section_id) {
                self::setParserValue(
                    "categories",
                    self::getCategories($setNews),
                    true
                );
            }

            self::setParserValue("usergroups", CAT_Users::getGroups(), true);
            $getGroupMembers =
                (int) (self::$parserValues["options"]["usergroup"] ?? 1);

            self::setParserValue(
                "users",
                CAT_Users::getMembers($getGroupMembers)
            );

            if (
                file_exists(
                    CAT_PATH .
                        "/modules/" .
                        static::$directory .
                        "/modify/" .
                        self::getVariant() .
                        "/modify.php"
                )
            ) {
                include CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/modify/" .
                    self::getVariant() .
                    "/modify.php";
            } elseif (
                file_exists(
                    CAT_PATH .
                        "/modules/" .
                        static::$directory .
                        "/modify/default/modify.php"
                )
            ) {
                include CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/modify/default/modify.php";
            }

            $parser->setPath(
                CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/templates/" .
                    self::getVariant()
            );
            $parser->setFallbackPath(
                CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/templates/default"
            );

            #if( !self::$parsed )
            $parser->output(static::$template, self::getParserValue());

            #self::$parsed = true;
        }

        /**
         *
         */
        public static function setParserValue(
            $name = null,
            $value = null,
            $reset = false
        ) {
            if (count(self::$parserValues) == 0) {
                self::$parserValues = [
                    "CAT_ADMIN_URL" => CAT_ADMIN_URL,
                    "CAT_PATH" => CAT_PATH,
                    "CAT_URL" => CAT_URL,
                    "page_id" => self::$page_id,
                    "section_id" => self::$section_id,
                    "version" => CAT_Helper_Addons::getModuleVersion(
                        static::$directory
                    ),
                    "variants" => self::getAllVariants(),
                    "variant" => self::getVariant(),
                    "bc_WYSIWYG" => [
                        "width" => "100%",
                        "height" => "150px",
                        "name" => "wysiwyg_" . self::$section_id,
                    ],
                ];
            }

            if ($name) {
                self::$parserValues[$name] =
                    isset(self::$parserValues[$name]) &&
                    is_array(self::$parserValues[$name]) &&
                    !$reset
                        ? array_merge(self::$parserValues[$name], $value)
                        : $value;
            }
        }

        /**
         *
         */
        public static function getParserValue()
        {
            if (count(self::$parserValues) == 0) {
                self::setParserValue();
            }
            return self::$parserValues;
        }

        /**
         *
         */
        public static function save()
        {
            if (CAT_Helper_Validate::sanitizePost("_cat_ajax") == 1) {
                header("Content-type: application/json");
                $backend = CAT_Backend::getInstance(
                    "Pages",
                    "pages_modify",
                    false
                );
            } else {
                $backend = CAT_Backend::getInstance("Pages", "pages_modify");
            }

            $backend->updateWhenModified();

            $action = CAT_Helper_Validate::sanitizePost("action");
            $return = [];
            self::setIDs();

            switch ($action) {
                case "setSkin":
                    $return = self::setOption(
                        "variant",
                        CAT_Helper_Validate::sanitizePost("variant")
                    );
                    break;
                case "get":
                    $return = blackNewsEntry::getEntry();
                    break;
                case "add":
                    $return = blackNewsEntry::addEntry();
                    break;
                case "copy":
                    $return = blackNewsEntry::copyEntry();
                    break;
                case "publish":
                    $return = blackNewsEntry::publishEntry();
                    break;
                case "remove":
                    $return = blackNewsEntry::removeEntry();
                    break;
                case "saveOptions":
                    $return = self::saveOption();
                    break;
                case "orderEntries":
                    $return = [
                        "message" => "Einträge sortiert",
                        "success" => self::order(
                            CAT_Helper_Validate::sanitizePost("positions")
                        ),
                    ];
                    break;
                case "uploadIMG":
                    if (
                        isset($_FILES["bNimage"]["name"]) &&
                        $_FILES["bNimage"]["name"] != ""
                    ) {
                        $return = [
                            "message" => $backend
                                ->lang()
                                ->translate("Image uploaded successfully!"),
                            "success" => blackNewsEntry::saveImage($_FILES),
                        ];
                    } else {
                        $ajax_return = [
                            "message" => $backend
                                ->lang()
                                ->translate("No images to upload"),
                            "success" => false,
                        ];
                    }
                    break;
                case "removeIMG":
                    /*$deleted	= blackNewsEntry::removeImage();
					$return	= array(
						'message'	=> $deleted === true
							? $lang->translate( 'Image deleted successfully!' )
							: $lang->translate( 'An error occoured!' ),
						'success'	=> $deleted
					);*/
                    $return = [
                        "message" => "delete",
                        "success" => true,
                    ];
                    break;

                default:
                    // save
                    $return = blackNewsEntry::saveEntry();
                    break;
            }

            if (CAT_Helper_Validate::sanitizePost("_cat_ajax") == 1) {
                print json_encode($return);
                exit();
            } else {
                $backend->print_success(
                    isset($return["message"])
                        ? $return["message"]
                        : $backend->lang()->translate("Saved successfully"),
                    CAT_ADMIN_URL .
                        "/pages/modify.php?page_id=" .
                        self::$page_id
                );
            }
        }
        /**
         *
         */
        public static function checkNewsInSection($pageID): bool
        {
            $result = self::getInstance()
                ->db()
                ->query(
                    "SELECT `entryID` " .
                        "FROM `:prefix:mod_blackNewsEntry`, `:prefix:sections` " .
                        "WHERE `:prefix:sections`.`page_id` = :page_id " .
                        'AND `:prefix:sections`.`module`="blacknews" ' .
                        "AND `:prefix:sections`.`section_id` = `:prefix:mod_blackNewsEntry`.`section_id`",
                    [
                        "page_id" => $pageID,
                    ]
                );
            $id = false;
            if ($result && $result->rowCount() > 0) {
                while (false !== ($option = $result->fetch())) {
                    $id = $option["entryID"];
                }
            }
            return $id ? true : false;
        }
        /**
         *
         */
        public static function uninstall()
        {
            $errors = self::sqlProcess(
                $CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/inc/uninstall.sql"
            );
            return $errors;
        }

        /**
         *
         */
        public static function upgrade()
        {
            // TODO: implement here
        }
    }
}
