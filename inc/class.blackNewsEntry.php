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

if (!class_exists("blackNewsEntry", false)) {
    if (!class_exists("blackNews", false)) {
        include dirname(__FILE__) . "/class.blackNews.php";
    }

    class blackNewsEntry extends blackNews
    {
        /**
         * @var void
         */
        private static $entryID;
        private static $typ;
        protected static $options = [];
        private static $info = [];
        private static $values = [];
        private static $seoUrl;

        private static $staticVars = [
            "staticVars",
            "modified",
            "eventID",
            "timestamp",
            "instance",
        ];

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();
        }
        public function __destruct()
        {
            parent::__destruct();
        }
        /**
         * @param void $entryID
         */
        public static function getInstance($entryID = null)
        {
            if (!self::$instance || $entryID) {
                self::$instance = new self();
            }
            if ($entryID) {
                self::$entryID = $entryID;
            }
            return self::$instance;
        }

        /**
         * @param void
         */
        public static function getEntry()
        {
            return array_merge(self::getEntryInfo(), [
                "entryID" => self::getEntryID(),
                "image" => self::getImage(),
                "message" => "Eintrag geladen",
                "options" => self::getOption(),
                "category" => self::getCategory(),
                "success" => true,
            ]);
        }
        /**
         * @param void
         */
        public static function getEntryByURL($seoURL = null)
        {
            if (!$seoURL) {
                return false;
            }

            self::$entryID = self::getInstance()
                ->db()
                ->query(
                    'SELECT `entryID` FROM `:prefix:mod_blackNewsEntry`
					WHERE `seoURL` = :seoURL',
                    [
                        "seoURL" => $seoURL,
                    ]
                )
                ->fetchColumn();
            return self::getEntry();
        } // end getEntryByURL()

        /**
         * @param void $name
         * @param void $value
         */
        public static function getImage()
        {
            if (
                file_exists(
                    CAT_PATH .
                        parent::$imageDir .
                        parent::$imageName .
                        "_" .
                        self::getEntryID() .
                        ".jpg"
                )
            ) {
                return CAT_URL .
                    parent::$imageDir .
                    parent::$imageName .
                    "_" .
                    self::getEntryID() .
                    ".jpg";
            } else {
                return false;
            }
        }

        /**
         * @param void $name
         * @param void $value
         */
        public static function getOption($name = null, $fromDB = false)
        {
            if ($name) {
                if (isset(self::$options[$name])) {
                    return self::$options[$name];
                }

                // Get info from table
                return self::getInstance()
                    ->db()
                    ->query(
                        "SELECT `value` FROM `:prefix:mod_blackNewsEntryOptions` " .
                            "WHERE `entryID` = :entryID AND `name` = :name",
                        [
                            "entryID" => self::getEntryID(),
                            "name" => $name,
                        ]
                    );
            } else {
                // Get all options
                $result = self::getInstance()
                    ->db()
                    ->query(
                        "SELECT `value`, `name` " .
                            "FROM `:prefix:mod_blackNewsEntryOptions` " .
                            "WHERE `entryID` = :entryID",
                        [
                            "entryID" => self::getEntryID(),
                        ]
                    );
                if ($result && $result->rowCount() > 0) {
                    while (false !== ($option = $result->fetch())) {
                        self::$options[$option["name"]] = $option["value"];
                    }
                }
                return self::$options;
            }
        }

        /**
         * @param void $name
         * @param void $value
         */
        private static function setOption($name, $value)
        {
            // Get info from table
            return self::getInstance()
                ->db()
                ->query(
                    "INSERT INTO `:prefix:mod_blackNewsEntryOptions` " .
                        "(`entryID`, `name`, `value`) VALUES ( :entryID, :name, :value ) " .
                        "ON DUPLICATE KEY UPDATE `value` = :value",
                    [
                        "entryID" => self::getEntryID(),
                        "name" => $name,
                        "value" => $value,
                    ]
                );
        }

        /**
         * @param void $name
         * @param void $value
         */
        public static function getEntryInfo($name = null)
        {
            $getEntry = self::getInstance()
                ->db()
                ->query(
                    "SELECT `entryID`, `section_id`, `title`, `content`, `text`, `modified`, `created`, `userID`, `seoURL`, `position`, `publish`, " .
                        'DATE_FORMAT(`publishDate`, "%Y-%m-%d") AS publishDate, ' .
                        'DATE_FORMAT(`publishDate`, "%H:%i") AS publishTime, ' .
                        'DATE_FORMAT(`unpublishDate`, "%Y-%m-%d") AS unpublishDate, ' .
                        'DATE_FORMAT(`unpublishDate`, "%H:%i") AS unpublishTime ' .
                        "FROM `:prefix:mod_blackNewsEntry` " .
                        "WHERE `entryID` = :entryID",
                    [
                        "entryID" => self::getEntryID(),
                    ]
                );

            self::$info = [];

            if (
                $getEntry &&
                $getEntry->numRows() > 0 &&
                false !== ($row = $getEntry->fetchRow())
            ) {
                self::$info = $row;
            }

            self::$info["userID"] = self::$info["userID"] ?? null;
            self::$info["publish"] = self::$info["publish"] ?? null;
            self::$info["publishDate"] = self::$info["publishDate"] ?? "";
            self::$info["publishTime"] = self::$info["publishTime"] ?? "";
            self::$info["unpublishDate"] = self::$info["unpublishDate"] ?? "";
            self::$info["unpublishTime"] = self::$info["unpublishTime"] ?? "";

            if (!empty(self::$info["userID"])) {
                $username = CAT_Users::getInstance()->get_user_details(
                    self::$info["userID"],
                    "username"
                );
                $displayName = CAT_Users::getInstance()->get_user_details(
                    self::$info["userID"],
                    "display_name"
                );

                self::$info["username"] = $username ?: "unknown";
                self::$info["display_name"] = $displayName ?: "Unknown";
            } else {
                self::$info["username"] = "unknown";
                self::$info["display_name"] = "Unknown";
            }

            return $name ? self::$info[$name] ?? null : self::$info;
        }
        /**
         * Fill the object with the values of an event from database
         *
         * @access public
         * @param  bool		$returnArray	- option whether an array should be returned
         * @return object/array
         *
         **/
        public function getEvent($returnArray = null)
        {
            // Get info from table
            self::$info = self::getInstance()
                ->db()
                ->query(
                    "SELECT `entryID`, `section_id`, `title`, `content`, `text`, `modified`, `created`, `userID`, `seoURL`, `position`, `publishDate`, `unpublishDate`, `publish` FROM `:prefix:mod_blackNewsEntry` " .
                        "WHERE `entryID` = :entryID",
                    [
                        "entryID" => self::getEntryID(),
                    ]
                )
                ->fetchRow();

            if (
                isset($getEvent) &&
                $getEvent->numRows() > 0 &&
                !false == ($row = $getEvent->fetchRow())
            ) {
                $this->setProperty("calID", $row["calID"]);
                $this->setProperty("publishDate", $row["publishDate"]);
                $this->setProperty(
                    "username",
                    CAT_Users::getInstance()->get_user_details(
                        $info["userID"],
                        "username"
                    )
                );
                $this->setProperty(
                    "display_name",
                    CAT_Users::getInstance()->get_user_details(
                        $info["userID"],
                        "display_name"
                    )
                );
                $this->setProperty("kind", $row["kind"]);
                $this->setProperty("start", $row["start"]);
                $this->setProperty("end", $row["end"]);
                $this->setProperty("timestamp", $row["timestamp"]);
                $this->setProperty("eventURL", $row["eventURL"]);
                $this->setProperty("UID", $row["UID"]);
                $this->setProperty("publish", $row["publish"]);
                $this->setProperty("allday", $row["allday"]);
                $this->setProperty("modified", $row["modified"]);
                $this->setProperty("createdID", $row["userID"]);
                $this->setProperty("publishDate", $row["publishDate"]);
                $this->setProperty("unpublishDate", $row["unpublishDate"]);
                $this->setProperty("publishTime", $row["publishTime"]);
                $this->setProperty("unpublishTime", $row["unpublishTime"]);
                $this->setProperty("modifiedID", $row["userID"]);
            }
            if ($returnArray) {
                return $this->createReturnArray();
            } else {
                return $this;
            }
        }

        /**
         * create the array for callback if needed
         *
         * @access private
         * @return array
         *
         **/
        private function createReturnArray()
        {
            return [
                "calID" => $this->getProperty("calID"),
                "location" => $this->getProperty("location"),
                "title" => $this->getProperty("title"),
                "description" => $this->getProperty("description"),
                "kind" => $this->getProperty("kind"),
                "start_date" => $this->getDateTimeInput("start"),
                "start_day" => $this->getDateTimeInput("start", "%d"),
                "start_time" => $this->getDateTimeInput("start", "%H:%M"),
                "end_date" => $this->getDateTimeInput("end"),
                "end_day" => $this->getDateTimeInput("end", "%d"),
                "end_time" => $this->getDateTimeInput("end", "%H:%M"),
                "timestamp" => $this->getProperty("timestamp"),
                "eventURL" => $this->getProperty("eventURL"),
                "UID" => $this->getProperty("UID"),
                "publish" => $this->getProperty("publish"),
                "allday" => $this->getProperty("allday"),
                "timestampDate" => $this->getDateTimeInput(
                    "timestamp",
                    "%d.%m.%Y"
                ),
                "timestampTime" => $this->getDateTimeInput(
                    "timestamp",
                    "%H:%M"
                ),
                "modifiedDate" => $this->getDateTimeInput("modified"),
                "modifiedTime" => $this->getDateTimeInput("modified", "%H:%M"),
                "publishDate" => $this->getProperty("publishDate"),
                "unpublishDate" => $this->getProperty("unpublishDate"),
                "publishTime" => $this->getProperty("publishTime"),
                "unpublishTime" => $this->getProperty("unpublishTime"),
                "createdID" => CAT_Users::get_user_details(
                    $this->getProperty("createdID"),
                    "display_name"
                ),
                "modifiedID" => CAT_Users::get_user_details(
                    $this->getProperty("modifiedID"),
                    "display_name"
                ),
            ];
        }

        /**
         * @param void $name
         * @param void $value
         */
        public static function setEntryInfo(array $values)
        {
            foreach ($values as $k => $v) {
                self::$info[$k] = $v;
            }

            $i = 0;
            while (
                self::$info["seoURL"] == "" ||
                ($checkID =
                    self::getInstance()
                        ->db()
                        ->query(
                            "SELECT `entryID` FROM `:prefix:mod_blackNewsEntry` " .
                                "WHERE `seoURL` = :seoURL",
                            [
                                "seoURL" => self::$info["seoURL"],
                            ]
                        )
                        ->fetchColumn() &&
                    self::getInstance()
                        ->db()
                        ->query(
                            "SELECT `entryID` FROM `:prefix:mod_blackNewsEntry` " .
                                "WHERE `seoURL` = :seoURL",
                            [
                                "seoURL" => self::$info["seoURL"],
                            ]
                        )
                        ->fetchColumn() != self::getEntryID())
            ) {
                self::$info["seoURL"] =
                    self::$info["seoURL"] == ""
                        ? parent::createTitleURL(self::$info["title"])
                        : parent::createTitleURL(self::$info["title"]) .
                            "-" .
                            ++$i;
            }
            self::$info["userID"] = !empty($values["userID"])
                ? $values["userID"]
                : CAT_Users::getInstance()->get_user_id();

            // use HTMLPurifier to clean up the contents if enabled
            if (
                self::getInstance()
                    ->db()
                    ->get_one(
                        "SELECT `value` FROM `:prefix:settings` " .
                            "WHERE `name` = 'enable_htmlpurifier' AND `value` = 'true'"
                    )
            ) {
                self::$info[
                    "wysiwyg"
                ] = CAT_Helper_Protect::getInstance()->purify(
                    self::$info["wysiwyg"],
                    ["Core.CollectErrors" => true]
                );
            }

            // Add a new entry
            $publishDate = self::$info["publishDate"] ?? "";
            $publishTime = self::$info["publishTime"] ?? "00:00";
            $unpublishDate = self::$info["unpublishDate"] ?? "";
            $unpublishTime = self::$info["unpublishTime"] ?? "00:00";

            $pD = null;
            if ($publishDate !== "") {
                $pD = date(
                    "Y-m-d H:i:s",
                    strtotime($publishDate . " " . $publishTime)
                );
            }

            $upD = null;
            if ($unpublishDate !== "") {
                $upD = date(
                    "Y-m-d H:i:s",
                    strtotime($unpublishDate . " " . $unpublishTime)
                );
            }

            return self::getInstance()
                ->db()
                ->query(
                    "INSERT INTO `:prefix:mod_blackNewsEntry` " .
                        "( `entryID`, `title`, `content`, `text`, `seoURL`, `publishDate`, `unpublishDate`, `userID` ) VALUES " .
                        "( :entryID, :title, :content, :text, :seoURL, :pD, :upD, :userID ) " .
                        "ON DUPLICATE KEY UPDATE " .
                        "`title` = :title, `content` = :content, `text` = :text, `seoURL` = :seoURL, `publishDate` = :pD, `unpublishDate` = :upD, `userID` = :userID",
                    [
                        "entryID" => self::getEntryID(),
                        "title" => self::$info["title"],
                        "content" => self::$info["wysiwyg"],
                        "text" => strip_tags(self::$info["wysiwyg"]),
                        "seoURL" => self::$info["seoURL"],
                        "pD" => $pD,
                        "upD" => $upD,
                        "userID" => self::$info["userID"],
                    ]
                );
        }

        /**
         *
         */
        public static function getEntryID()
        {
            if (!self::$entryID) {
                self::setEntryID();
            }
            return self::$entryID;
        }

        /**
         *
         */
        public static function setEntryID($entryID = null)
        {
            self::$entryID =
                $entryID && is_numeric($entryID)
                    ? $entryID
                    : CAT_Helper_Validate::sanitizePost("entryID", "numeric");
            return self::$entryID;
        }

        /**
         *
         */
        public function getDescription()
        {
            // TODO: implement here
        }

        /**
         *
         */
        public function setDescription($content)
        {
            // TODO: implement here
        }

        /**
         *
         */
        public function setCategoryByURL($url = null)
        {
            if (!$url) {
                return false;
            }

            // Set category
            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "REPLACE INTO `:prefix:mod_blackNewsCategoryEntries` " .
                            "( `catID`, `entryID` ) " .
                            "VALUES (" .
                            "( SELECT `catID` FROM `:prefix:mod_blackNewsCategoryEntries` " .
                            "WHERE `section_id` = :section_id AND `url` = :url ), " .
                            ":entryID )",
                        [
                            "url" => $url,
                            "section_id" => parent::$section_id,
                            "entryID" => self::getEntryID(),
                        ]
                    )
            ) {
            }
        }

        /**
         *
         */
        public static function getCategory()
        {
            #			if (isset(self::$options[$name])) return self::$options[$name];
            // Get all categories for this
            $result = self::getInstance()
                ->db()
                ->query(
                    "SELECT bnCE.`catID`, bnC.`url`, bnC.`category` " .
                        "FROM `:prefix:mod_blackNewsCategoryEntries` AS bnCE " .
                        "JOIN `:prefix:mod_blackNewsCategory` AS bnC ON bnC.`catID` = bnCE.`catID` " .
                        "WHERE `entryID` = :entryID",
                    [
                        "entryID" => self::getEntryID(),
                    ]
                );
            $cats = [];
            if ($result && $result->rowCount() > 0) {
                while (false !== ($option = $result->fetch())) {
                    $cats[] = [
                        "catID" => $option["catID"],
                        "category" => $option["category"],
                        "url" => $option["url"],
                    ];
                }
            }
            return $cats;
        }

        /**
         *
         */
        public static function setCategoryByID($catID = null)
        {
            if (!$catID) {
                return false;
            }

            $return = true;

            //
            if (!is_array($catID)) {
                $catID = [$catID];
            }

            // Delete all categories for this entry
            if (
                !self::getInstance()
                    ->db()
                    ->query(
                        "DELETE FROM `:prefix:mod_blackNewsCategoryEntries` " .
                            "WHERE `entryID` = :entryID",
                        [
                            "entryID" => self::getEntryID(),
                        ]
                    )
            ) {
                $return = false;
            }

            // Save only categories if "No category" is unchecked and a category is selected
            if (count($catID) > 0 && !in_array(-1, $catID)) {
                foreach ($catID as $id) {
                    // Set category
                    if (
                        !self::getInstance()
                            ->db()
                            ->query(
                                "REPLACE INTO `:prefix:mod_blackNewsCategoryEntries` " .
                                    "( `catID`, `entryID` ) " .
                                    "VALUES ( :catID, :entryID )",
                                [
                                    "catID" => $id,
                                    "entryID" => self::getEntryID(),
                                ]
                            )
                    ) {
                        $return = false;
                    }
                }
            }
            return $return;
        }

        /**
         *
         */
        public static function publishEntry()
        {
            self::getEntryID();

            // Set publish
            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "UPDATE `:prefix:mod_blackNewsEntry` " .
                            "SET `publish` = ( SELECT CASE " .
                            "WHEN `publish` IS NULL THEN CURRENT_TIMESTAMP " .
                            "ELSE NULL " .
                            "END AS publish ) " .
                            "WHERE `entryID` = :entryID",
                        [
                            "entryID" => self::getEntryID(),
                        ]
                    )
            ) {
                return [
                    "message" =>
                        self::getEntryInfo("publish") === null
                            ? "Eintrag offline"
                            : "Eintrag veröffentlicht",
                    "publish" => self::getEntryInfo("publish"),
                    "entryID" => self::getEntryID(),
                    "success" => true,
                ];
            } else {
                return [
                    "message" =>
                        self::getEntryInfo("publish") === null
                            ? "Eintrag offline"
                            : "Eintrag veröffentlicht",
                    "publish" => self::getEntryInfo("publish"),
                    "entryID" => self::getEntryID(),
                    "success" => true,
                ];
            }
        }

        /**
         *
         */
        public static function addEntry()
        {
            self::$info["title"] = self::createTitle();

            // Set publish
            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "INSERT INTO `:prefix:mod_blackNewsEntry` " .
                            "( `section_id`, `userID`, `title`, `position` ) " .
                            "VALUES ( :section_id, :userID, :title, " .
                            "( SELECT MAX(t.`position`) FROM (SELECT `position` FROM `:prefix:mod_blackNewsEntry`) t ) + 1 )",
                        [
                            "section_id" => parent::$section_id,
                            "userID" => CAT_Users::getInstance()->get_user_id(),
                            "title" => self::$info["title"],
                        ]
                    )
            ) {
                self::$entryID = self::getInstance()
                    ->db()
                    ->lastInsertId();

                return [
                    "message" => "Eintrag angelegt",
                    "html" => self::getHTML("entryList"),
                    "entryID" => self::getEntryID(),
                    "success" => true,
                ];
            }
        }

        /**
         *
         */
        public static function saveEntry()
        {
            self::$entryID = CAT_Helper_Validate::sanitizePost("entryID");

            self::setEntryInfo(CAT_Helper_Validate::sanitizePost("values"));

            self::setCategoryByID(
                CAT_Helper_Validate::sanitizePost("category")
            );

            if ($options = CAT_Helper_Validate::sanitizePost("options")) {
                foreach ($options as $opt) {
                    if (!isset($opt["name"])) {
                        continue;
                    }

                    self::setOption($opt["name"], $opt["value"] ?? "");
                }
            }
            return [
                "message" => "Eintrag gespeichert",
                "values" => self::$info,
                "entryID" => self::getEntryID(),
                "success" => true,
            ];
        }

        /**
         *
         */
        public static function removeEntry()
        {
            self::$entryID = CAT_Helper_Validate::sanitizePost(
                "entryID",
                "numeric"
            );

            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "DELETE FROM `:prefix:mod_blackNewsEntry` " .
                            "WHERE `entryID` = :entryID",
                        [
                            "entryID" => self::getEntryID(),
                        ]
                    )
            ) {
                return [
                    "message" => "Eintrag gelöscht",
                    "success" => true,
                ];
            }
        }

        /**
         *
         */
        public static function copyEntry()
        {
            $sourceID = self::getEntryID();

            self::getEntryInfo();
            self::getOption();

            // copy main information of entry
            if (
                self::getInstance()
                    ->db()
                    ->query(
                        "INSERT INTO `:prefix:mod_blackNewsEntry` " .
                            "( `section_id`, `title`, `content`, `text`, `userID` ) " .
                            "VALUES ( :section_id, :title, :content, :text, :userID )",
                        [
                            "section_id" => parent::$section_id,
                            "title" => self::$info["title"],
                            "content" => self::$info["content"],
                            "text" => self::$info["text"],
                            "userID" => CAT_Users::getInstance()->get_user_id(),
                        ]
                    )
            ) {
                self::setEntryID(
                    self::getInstance()
                        ->db()
                        ->lastInsertId()
                );

                // Copy options for entry
                foreach (self::$options as $key => $val) {
                    self::setOption($key, $val);
                }

                foreach (
                    CC_Form::getInstance()
                        ->setEntryID($sourceID)
                        ->getFields()
                    as $val
                ) {
                    CC_Form::getInstance()
                        ->setEntryID(self::getEntryID())
                        ->addField($val);
                }

                return [
                    "message" => "Eintrag kopiert",
                    "html" => self::getHTML("entryList"),
                    "entryID" => self::getEntryID(),
                    "success" => true,
                ];
            } else {
                return false;
            }
        }

        /**
         * save images
         *
         * @access public
         * @param  array  $tmpFiles - images in an array
         * @return boolean true/false
         *
         **/
        public static function saveImage($tmpFiles = null)
        {
            self::getEntryID();

            $field_name = "bNimage";

            if (
                isset($tmpFiles[$field_name]["name"]) &&
                $tmpFiles[$field_name]["name"] != ""
            ) {
                // ===========================================
                // ! Get file extension of the uploaded file
                // ===========================================
                $file_extension =
                    strtolower(
                        pathinfo(
                            $tmpFiles[$field_name]["name"],
                            PATHINFO_EXTENSION
                        )
                    ) == ""
                        ? false
                        : strtolower(
                            pathinfo(
                                $tmpFiles[$field_name]["name"],
                                PATHINFO_EXTENSION
                            )
                        );
                // ======================================
                // ! Check if file extension is allowed
                // ======================================
                if (
                    isset($file_extension) &&
                    in_array($file_extension, ["png", "jpg", "jpeg", "gif"])
                ) {
                    if (!is_array($tmpFiles) || !count($tmpFiles)) {
                        return CAT_Backend::getInstance("Pages", "pages_modify")
                            ->lang()
                            ->translate("No files!");
                    } else {
                        $current = CAT_Helper_Upload::getInstance(
                            $tmpFiles[$field_name]
                        );
                        if ($current->uploaded) {
                            $dir = CAT_PATH . parent::$imageDir;
                            $tempDir = $dir . "temp/";

                            if (!file_exists($dir) || !is_dir($dir)) {
                                CAT_Helper_Directory::createDirectory(
                                    $dir,
                                    null,
                                    true
                                );
                            }
                            if (!file_exists($tempDir) || !is_dir($tempDir)) {
                                CAT_Helper_Directory::createDirectory(
                                    $tempDir,
                                    null,
                                    true
                                );
                            }

                            $current->file_overwrite = true;
                            $current->process($tempDir);

                            if ($current->processed) {
                                #								$addImg	= $this->addImg( $file_extension );

                                if (
                                    !CAT_Helper_Image::getInstance()->make_thumb(
                                        $tempDir . $current->file_dst_name,
                                        $dir .
                                            parent::$imageName .
                                            "_" .
                                            self::getEntryID(),
                                        1400, //$resize_y,
                                        1400, //$resize_x,
                                        "fit",
                                        "jpg"
                                    )
                                ) {
                                    $return = false;
                                }

                                #								$this->createImg( $addImg['image_id'], self::$thumb_x, self::$thumb_y );

                                #								$addImg['thumb']	= sprintf( '%s/thumbs_%s_%s/',
                                #									$this->galleryURL,
                                #									self::$thumb_x,
                                #									self::$thumb_y ) . $addImg['picture'];

                                unlink($tempDir . $current->file_dst_name);

                                // =================================
                                // ! Clean the upload class $files
                                // =================================
                                $current->clean();
                                return true;
                            } else {
                                return CAT_Backend::getInstance(
                                    "Pages",
                                    "pages_modify"
                                )
                                    ->lang()
                                    ->translate(
                                        "File upload error: {{error}}",
                                        ["error" => $current->error]
                                    );
                            }
                        } else {
                            return CAT_Backend::getInstance(
                                "Pages",
                                "pages_modify"
                            )
                                ->lang()
                                ->translate("File upload error: {{error}}", [
                                    "error" => $current->error,
                                ]);
                        }
                    }
                }
            }
        } // end saveImages()

        /**
         *
         */
        public function getShort()
        {
            // TODO: implement here
        }

        /**
         *
         */
        public function getUrl()
        {
            // TODO: implement here
        }

        /**
         *
         */
        public function setUrl()
        {
            // TODO: implement here
        }

        /**
         *
         */
        private static function createTitle()
        {
            $result = self::getInstance()
                ->db()
                ->query(
                    "SELECT `title` FROM `:prefix:mod_blackNewsEntry` " .
                        "WHERE `section_id` = :section_id",
                    [
                        "section_id" => self::$section_id,
                    ]
                );

            $title = "Neuer Eintrag";
            $base = $title;

            if ($result && $result->rowCount() > 0) {
                $titles = [];
                while (false !== ($name = $result->fetch())) {
                    $titles[] = $name["title"];
                }
                $counter = 0;
                while (in_array($title, $titles)) {
                    $title = $base . "-" . ++$counter;
                }
            }
            return $title;
        }

        /**
         *
         */
        private static function getHTML($template = "entryList")
        {
            global $parser, $section_id;

            $parser_data["entry"] = self::$info;
            $parser_data["section_id"] = $section_id;
            $parser_data["entry"]["entryID"] = self::$entryID;
            $parser_data["CAT_URL"] = CAT_URL;

            self::$template = $template;

            $parser->setPath(
                CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/templates/" .
                    self::getVariant() .
                    "/modify/"
            );
            $parser->setFallbackPath(
                CAT_PATH .
                    "/modules/" .
                    static::$directory .
                    "/templates/default/modify/"
            );

            return $parser->get(self::$template, $parser_data);
        }

        /**
         * Prepare a valid string from a property for input:date
         *
         * @access private
         * @param  string	$prop	- property which should be converted
         * @param  string	$format	- output format
         * @return string
         *
         **/
        protected function getDateTimeInput(
            $prop = null,
            $format = "%Y-%m-%d %H:%M"
        ) {
            if (!self::getInstance()->getProperty($prop)) {
                return false;
            }
            return strftime(
                $format,
                strtotime(self::getInstance()->getProperty($prop))
            );
        }

        /**
         * Prepare a valid string from a property for DateTime in SQL
         *
         * @access private
         * @param  string	$prop	- property which should be converted
         * @return string
         *
         **/
        protected function getDateTimeSQL($prop = null)
        {
            if (!self::getInstance()->getProperty($prop)) {
                return false;
            }
            return strftime(
                "%Y-%m-%d %H:%M:00",
                strtotime(self::getInstance()->getProperty($prop))
            );
        }

        /**
         * Store a value to a property of an object
         *
         * @access public
         * @param  string	$key	- attribute of class, that should be set
         * @param  string	$value	- value for the attribute
         * @return object
         *
         **/
        public function setProperty($key = null, $value = null)
        {
            if (
                !self::getInstance()->getEventID() ||
                !property_exists("blackNewsEntry", $key) ||
                in_array($key, self::$staticVars)
            ) {
                return false;
            } else {
                self::getInstance()->$key = $value;
                return $this;
            }
        }

        /**
         * Get a value of a property of an object
         *
         * @access public
         * @param  string	$key	- attribute of class, that should be got
         * @return string
         *
         **/
        public function getProperty($key = null)
        {
            if (
                !self::getInstance()->getEventID() ||
                !property_exists("blackNewsEntry", $key) ||
                in_array($key, self::$staticVars)
            ) {
                return false;
            } else {
                return self::getInstance()->$key;
            }

            /*				'INSERT INTO `:prefix:mod_blacknews_content`
						(`page_id`, `section_id`, `news_id`, `title`, `subtitle`, `auto_generate_size`, `auto_generate` , `content`, `short`)
						VALUES (:page_id, :section_id, :news_id, :title, :subtitle, :auto_generate_size, :auto_generate, :content, :short )';*/
        }
    }
}
