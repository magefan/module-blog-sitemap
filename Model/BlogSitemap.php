<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\BlogSitemap\Model;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Robots\Model\Config\Value;
use Magefan\BlogSitemap\Model\ItemProvider\ItemProviderInterface;
use Magefan\BlogSitemap\Model\ResourceModel\BlogSitemap as BlogSitemapResource;

/**
 * BlogSitemap model.
 *
 * @method string getBlogsitemapType()
 * @method \Magefan\BlogSitemap\Model\BlogSitemap setBlogSitemapType(string $value)
 * @method string getBlogsitemapFilename()
 * @method \Magefan\BlogSitemap\Model\BlogSitemap setBlogSitemapFilename(string $value)
 * @method string getBlogsitemapPath()
 * @method \Magefan\BlogSitemap\Model\BlogSitemap setBlogSitemapPath(string $value)
 * @method string getBlogsitemapTime()
 * @method \Magefan\BlogSitemap\Model\BlogSitemap setBlogSitemapTime(string $value)
 * @method int getStoreId()
 * @method \Magefan\BlogSitemap\Model\BlogSitemap setStoreId(int $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @api
 * @since 2.0.0
 */
class BlogSitemap extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    public const OPEN_TAG_KEY = 'start';

    public const CLOSE_TAG_KEY = 'end';

    public const INDEX_FILE_PREFIX = 'blogsitemap';

    public const TYPE_INDEX = 'blogsitemap';

    public const TYPE_URL = 'url';

    private const ROOT_DIRECTORY = 'blogsitemap';

    /**
     * Last mode date min value
     */
    public const LAST_MOD_MIN_VAL = '0000-01-01 00:00:00';

    /**
     * Real file path
     *
     * @var string
     */
    protected $_filePath;

    /**
     * @var array
     */
    protected $_blogsitemapItems = [];

    /**
     * Current blogsitemap increment
     *
     * @var int
     */
    protected $_blogsitemapIncrement = 0;

    /**
     * BlogSitemap start and end tags
     *
     * @var array
     */
    protected $_tags = [];

    /**
     * Number of lines in blogsitemap
     *
     * @var int
     */
    protected $_lineCount = 0;

    /**
     * Current blogsitemap file size
     *
     * @var int
     */
    protected $_fileSize = 0;

    /**
     * New line possible symbols
     *
     * @var array
     */
    private $_crlf = ["win" => "\r\n", "unix" => "\n", "mac" => "\r"];

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_stream;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magefan\Blog\Model\ResourceModel\PostFactory
     */
    protected $_postFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @inheritdoc
     *
     * @since 2.0.0
     *
     * @var string|array|bool
     */
    protected $_cacheTag = [Value::CACHE_TAG];

    /**
     * Item resolver
     *
     * @var ItemProviderInterface
     */
    private $itemProvider;

    /**
     * BlogSitemap config reader
     *
     * @var BlogSitemapConfigReaderInterface
     */
    private $configReader;

    /**
     * @var \Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory
     */
    private $blogsitemapItemFactory;

    /**
     * Last mode min timestamp value
     *
     * @var int
     */
    private $lastModMinTsVal;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DocumentRoot
     */
    private $documentRoot;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param Filesystem $filesystem
     * @param \Magefan\Blog\Model\ResourceModel\CategoryFactory $categoryFactory
     * @param \Magefan\Blog\Model\ResourceModel\PostFactory $postFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DocumentRoot|null $documentRoot
     * @param ItemProviderInterface|null $itemProvider
     * @param BlogSitemapConfigReaderInterface|null $configReader
     * @param BlogSitemapItemInterfaceFactory|null $blogsitemapItemFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filesystem $filesystem,
        \Magefan\Blog\Model\ResourceModel\CategoryFactory $categoryFactory,
        \Magefan\Blog\Model\ResourceModel\PostFactory $postFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?\Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null,
        ?ItemProviderInterface $itemProvider = null,
        ?BlogSitemapConfigReaderInterface $configReader = null,
        ?\Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory $blogsitemapItemFactory = null
    ) {
        $this->_escaper = $escaper;
        $this->filesystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
        $this->_categoryFactory = $categoryFactory;
        $this->_postFactory = $postFactory;
        $this->_dateModel = $modelDate;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->dateTime = $dateTime;
        $this->itemProvider = $itemProvider ?: ObjectManager::getInstance()->get(ItemProviderInterface::class);
        $this->configReader = $configReader ?: ObjectManager::getInstance()->get(BlogSitemapConfigReaderInterface::class);
        $this->blogsitemapItemFactory = $blogsitemapItemFactory ?: ObjectManager::getInstance()->get(
            \Magefan\BlogSitemap\Model\BlogSitemapItemInterfaceFactory::class
        );

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BlogSitemapResource::class);
    }

    /**
     * Get file handler
     *
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws LocalizedException
     */
    protected function _getStream()
    {
        if ($this->_stream) {
            return $this->_stream;
        } else {
            throw new LocalizedException(__('File handler unreachable'));
        }
    }

    /**
     * Initialize blogsitemap
     *
     * @return void
     */
    protected function _initBlogSitemapItems()
    {
        $blogsitemapItems = $this->itemProvider->getItems($this->getStoreId());
        $mappedItems = $this->mapToBlogSitemapItem();
        $this->_blogsitemapItems = array_merge($blogsitemapItems, $mappedItems);

        $this->_tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<blogsitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</blogsitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                    ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * Check blogsitemap file location and permissions
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $path = $this->getBlogsitemapPath();

        /**
         * Ensure root blogsitemap directory exists.
         */
        $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)
            ->create(self::ROOT_DIRECTORY);

        /**
         * Check path is allow
         */
        if ($path && preg_match('#\.\.[\\\/]#', $path)) {
            throw new LocalizedException(__('Please define a correct path.'));
        }
        /**
         * Check exists and writable path
         */
        if (!$this->_directory->isExist($path)) {
            throw new LocalizedException(
                __(
                    'Please create the specified folder "%1" before saving the blogsitemap.',
                    $this->_escaper->escapeHtml($this->getBlogsitemapPath())
                )
            );
        }

        if (!$this->_directory->isWritable($path)) {
            throw new LocalizedException(
                __('Please make sure that "%1" is writable by the web-server.', $this->getBlogsitemapPath())
            );
        }
        /**
         * Check allow filename
         */
        if ($this->getBlogsitemapFilename() === null || !preg_match('#^[a-zA-Z0-9_\.]+$#', $this->getBlogsitemapFilename())) {
            throw new LocalizedException(
                __(
                    'Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename.'
                    . ' No spaces or other characters are allowed.'
                )
            );
        }
        if (!preg_match('#\.xml$#', $this->getBlogsitemapFilename())) {
            $this->setBlogSitemapFilename($this->getBlogsitemapFilename() . '.xml');
        }

        $this->setBlogSitemapPath(rtrim(str_replace(str_replace('\\', '/', $this->_getBaseDir()), '', $path), '/') . '/');

        return parent::beforeSave();
    }

    /**
     * Generate XML file
     *
     * @see http://www.blogsitemaps.org/protocol.html
     *
     * @return $this
     */
    public function generateXml(): static
    {
        $this->_initBlogSitemapItems();

        /** @var $item BlogSitemapItemInterface */
        foreach ($this->_blogsitemapItems as $item) {
            $xml = $this->_getBlogsitemapRow(
                $item->getUrl(),
                $item->getUpdatedAt(),
                $item->getChangeFrequency(),
                $item->getPriority(),
                $item->getImages()
            );

            if ($this->_isSplitRequired($xml) && $this->_blogsitemapIncrement > 0) {
                $this->_finalizeBlogSitemap();
            }

            if (!$this->_fileSize) {
                $this->_createBlogSitemap();
            }

            $this->_writeBlogSitemapRow($xml);
            // Increase counters
            $this->_lineCount++;
            $this->_fileSize += strlen($xml);
        }

        $this->_finalizeBlogSitemap();

        if ($this->_blogsitemapIncrement == 1) {
            // In case when only one increment file was created use it as default blogsitemap
            $blogsitemapPath = $this->getBlogsitemapPath() !== null ? rtrim($this->getBlogsitemapPath(), '/') : '';
            $path = $blogsitemapPath . '/' . $this->_getCurrentBlogSitemapFilename($this->_blogsitemapIncrement);
            $destination = $blogsitemapPath . '/' . $this->getBlogsitemapFilename();

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated blogsitemaps
            $this->_createBlogSitemapIndex();
        }

        $this->setBlogSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }

    /**
     * Generate blogsitemap index XML file
     *
     * @return void
     */
    protected function _createBlogSitemapIndex()
    {
        $this->_createBlogSitemap($this->getBlogsitemapFilename(), self::TYPE_INDEX);
        for ($i = 1; $i <= $this->_blogsitemapIncrement; $i++) {
            $xml = $this->_getBlogsitemapIndexRow($this->_getCurrentBlogSitemapFilename($i), $this->_getCurrentDateTime());
            $this->_writeBlogSitemapRow($xml);
        }
        $this->_finalizeBlogSitemap(self::TYPE_INDEX);
    }

    /**
     * Get current date time
     *
     * @return string
     */
    protected function _getCurrentDateTime(): string
    {
        return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Check is split required
     *
     * @param string $row
     * @return bool
     */
    protected function _isSplitRequired($row): bool
    {
        $storeId = $this->getStoreId();
        if ($this->_lineCount + 1 > $this->configReader->getMaximumLinesNumber($storeId)) {
            return true;
        }

        if ($this->_fileSize + strlen($row) > $this->configReader->getMaximumFileSize($storeId)) {
            return true;
        }

        return false;
    }

    /**
     * Get blogsitemap row
     *
     * @param string $url
     * @param null|string $lastmod
     * @param null|string $changefreq
     * @param null|string $priority
     * @param null|array|\Magento\Framework\DataObject $images
     * @return string
     * BlogSitemap images
     * @see http://support.google.com/webmasters/bin/answer.py?hl=en&answer=178636
     *
     * BlogSitemap PageMap
     * @see http://support.google.com/customsearch/bin/answer.py?hl=en&answer=1628213
     */
    protected function _getBlogsitemapRow($url, $lastmod = null, $changefreq = null, $priority = null, $images = null): string
    {
        $url = $this->_getUrl($url);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }
        if ($changefreq) {
            $row .= '<changefreq>' . $this->_escaper->escapeHtml($changefreq) . '</changefreq>';
        }
        if ($priority) {
            $row .= sprintf('<priority>%.1f</priority>', $this->_escaper->escapeHtml($priority));
        }
        if ($images) {
            // Add Images to blogsitemap
            foreach ($images as $image) {
                if (!$image) {
                    continue;
                }
                $row .= '<image:image>';
                $row .= '<image:loc>' . $this->_escaper->escapeUrl($image) . '</image:loc>';
                /*
                $row .= '<image:title>' . $this->escapeXmlText($images->getTitle()) . '</image:title>';
                if ($image->getCaption()) {
                    $row .= '<image:caption>' . $this->escapeXmlText($image->getCaption()) . '</image:caption>';
                }
                */
                $row .= '</image:image>';
            }
            // Add PageMap image for Google web search
            /*
            $row .= '<PageMap xmlns="http://www.google.com/schemas/blogsitemap-pagemap/1.0"><DataObject type="thumbnail">';
            $row .= '<Attribute name="name" value="' . $this->_escaper->escapeHtmlAttr($images->getTitle()) . '"/>';
            $row .= '<Attribute name="src" value="' . $this->_escaper->escapeUrl($images->getThumbnail()) . '"/>';
            $row .= '</DataObject></PageMap>';
            */
        }

        return '<url>' . $row . '</url>';
    }

    /**
     * Escape string for XML context.
     *
     * @param string $text
     * @return string
     */
    private function escapeXmlText(string $text): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($doc->createTextNode($text));
        return $doc->saveXML($fragment);
    }

    /**
     * Get blogsitemap index row
     *
     * @param string $blogsitemapFilename
     * @param null|string $lastmod
     * @return string
     */
    protected function _getBlogsitemapIndexRow($blogsitemapFilename, $lastmod = null): string
    {
        $url = $this->getBlogsitemapUrl($this->getBlogsitemapPath(), $blogsitemapFilename);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';
        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }

        return '<blogsitemap>' . $row . '</blogsitemap>';
    }

    /**
     * Create new blogsitemap file
     *
     * @param null|string $fileName
     * @param string $type
     * @return void
     * @throws LocalizedException
     */
    protected function _createBlogSitemap($fileName = null, $type = self::TYPE_URL)
    {
        if (!$fileName) {
            $this->_blogsitemapIncrement++;
            $fileName = $this->_getCurrentBlogSitemapFilename($this->_blogsitemapIncrement);
        }

        $path = ($this->getBlogsitemapPath() !== null ? rtrim($this->getBlogsitemapPath(), '/') : '') . '/' . $fileName;
        $this->_stream = $this->_directory->openFile($path);

        $fileHeader = sprintf($this->_tags[$type][self::OPEN_TAG_KEY], $type);
        $this->_stream->write($fileHeader);
        $this->_fileSize = strlen($fileHeader . sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
    }

    /**
     * Write blogsitemap row
     *
     * @param string $row
     * @return void
     */
    protected function _writeBlogSitemapRow(string $row)
    {
        $this->_getStream()->write($row . PHP_EOL);
    }

    /**
     * Write closing tag and close stream
     *
     * @param string $type
     * @return void
     */
    protected function _finalizeBlogSitemap($type = self::TYPE_URL)
    {
        if ($this->_stream) {
            $this->_stream->write(sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
            $this->_stream->close();
        }

        // Reset all counters
        $this->_lineCount = 0;
        $this->_fileSize = 0;
    }

    /**
     * Get current blogsitemap filename
     *
     * @param int $index
     * @return string
     */
    protected function _getCurrentBlogSitemapFilename($index): string
    {
        return ($this->getBlogsitemapFilename() !== null ? str_replace('.xml', '', $this->getBlogsitemapFilename()) : '')
            . '-' . $this->getStoreId() . '-' . $index . '.xml';
    }

    /**
     * Get base dir
     *
     * @return string
     */
    protected function _getBaseDir()
    {
        return $this->_directory->getAbsolutePath();
    }

    /**
     * Get store base url
     *
     * @param string $type
     * @return string
     */
    protected function _getStoreBaseUrl($type = UrlInterface::URL_TYPE_LINK): string
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore($this->getStoreId());
        $isSecure = $store->isUrlSecure();
        return rtrim($store->getBaseUrl($type, $isSecure), '/') . '/';
    }

    /**
     * Get url
     *
     * @param string $url
     * @param string $type
     * @return string
     */
    protected function _getUrl($url, $type = UrlInterface::URL_TYPE_LINK): string
    {
        return $this->_getStoreBaseUrl($type) . ($url !== null ? ltrim($url, '/') : '');
    }

    /**
     * Get date in correct format applicable for lastmod attribute
     *
     * @param string $date
     * @return string
     */
    protected function _getFormattedLastmodDate($date): string
    {
        if ($this->lastModMinTsVal === null) {
            $this->lastModMinTsVal = strtotime(self::LAST_MOD_MIN_VAL);
        }
        $timestamp = max(strtotime($date), $this->lastModMinTsVal);
        return date('c', $timestamp);
    }

    /**
     * Get Document root of Magento instance
     *
     * @return string
     */
    protected function _getDocumentRoot()
    {
        if (PHP_SAPI === 'cli') {
            return $this->getDocumentRootFromBaseDir() ?? '';
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return realpath($this->_request->getServer('DOCUMENT_ROOT'));
    }

    /**
     * Get domain from store base url
     *
     * @return string
     */
    protected function _getStoreBaseDomain(): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $storeParsedUrl = parse_url($this->_getStoreBaseUrl());
        $url = $storeParsedUrl['scheme'] . '://' . $storeParsedUrl['host'];

        // Set document root to false if we were unable to get it
        $documentRoot = $this->_getDocumentRoot() ?: false;
        if ($documentRoot) {
            $documentRoot = trim(str_replace(DIRECTORY_SEPARATOR, '/', $documentRoot), '/');
        }
        $baseDir = trim(str_replace(DIRECTORY_SEPARATOR, '/', $this->_getBaseDir()), '/');

        if ($documentRoot !== false && strpos($baseDir, (string) $documentRoot) === 0) {
            //case when basedir is in document root
            $installationFolder = trim(str_replace($documentRoot, '', $baseDir), '/');
            $storeDomain = rtrim($url . '/' . $installationFolder, '/');
        } else {
            //case when documentRoot contains symlink to basedir
            $url = $this->_getStoreBaseUrl(UrlInterface::URL_TYPE_WEB);
            $storeDomain = rtrim($url, '/');
        }

        return $storeDomain;
    }

    /**
     * Get blogsitemap.xml URL according to all config options
     *
     * @param string $blogsitemapPath
     * @param string $blogsitemapFileName
     * @return string
     */
    public function getBlogsitemapUrl(string $blogsitemapPath, string $blogsitemapFileName)
    {
        return $this->_getStoreBaseDomain() . str_replace('//', '/', $blogsitemapPath . '/' . $blogsitemapFileName);
    }

    /**
     * Find new lines delimiter
     *
     * @param string $text
     * @return string
     */
    private function _findNewLinesDelimiter($text)
    {
        foreach ($this->_crlf as $delimiter) {
            if (strpos($text, (string) $delimiter) !== false) {
                return $delimiter;
            }
        }

        return PHP_EOL;
    }

    /**
     * BlogSitemap item mapper for backwards compatibility
     *
     * @return array
     */
    private function mapToBlogSitemapItem(): array
    {
        $items = [];

        foreach ($this->_blogsitemapItems as $data) {
            foreach ($data->getCollection() as $item) {
                $items[] = $this->blogsitemapItemFactory->create(
                    [
                        'url' => $item->getUrl(),
                        'updatedAt' => $item->getUpdatedAt(),
                        'images' => $item->getImages(),
                        'priority' => $data->getPriority(),
                        'changeFrequency' => $data->getChangeFrequency(),
                    ]
                );
            }
        }

        return $items;
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        return [
            Value::CACHE_TAG . '_' . $this->getStoreId(),
        ];
    }

    /**
     * Get document root using base directory (root directory) and base path (base url path)
     *
     * Document root is determined using formula: BaseDir = DocumentRoot + BasePath.
     * Returns <b>NULL</b> if BaseDir does not end with BasePath (e.g document root contains a symlink to BaseDir).
     *
     * @return string|null
     */
    private function getDocumentRootFromBaseDir(): ?string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $basePath = rtrim(parse_url($this->_getStoreBaseUrl(UrlInterface::URL_TYPE_WEB), PHP_URL_PATH) ?: '', '/');
        $basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $baseDir = rtrim($this->_getBaseDir(), DIRECTORY_SEPARATOR);
        $length = strlen($basePath);
        if (!$length) {
            $documentRoot = $baseDir;
        } elseif (substr($baseDir, -$length) === $basePath) {
            $documentRoot = rtrim(substr($baseDir, 0, strlen($baseDir) - $length), DIRECTORY_SEPARATOR);
        } else {
            $documentRoot = null;
        }
        return $documentRoot;
    }
}
