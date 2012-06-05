<?php

namespace Rts\Bundle\AppMonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Rts\Bundle\AppMonBundle\Entity\App
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Rts\Bundle\AppMonBundle\Entity\AppRepository")
 */
class App
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string $api_url
     *
     * @ORM\Column(name="api_url", type="string", length=255)
     */
    private $api_url;

    /**
     * @var text $meta_local_data
     *
     * @ORM\Column(name="meta_local_data", type="text", nullable=true)
     */
    private $meta_local_data;

    /**
     * @var text $meta_data_json
     *
     * @ORM\Column(name="meta_data_json", type="text", nullable=true)
     */
    private $meta_data_json;

    /**
     * @var string $version
     *
     * @ORM\Column(name="version", type="string", length=255, nullable=true)
     */
    private $version = null;

    /**
     * @var smallint $http_status
     *
     * @ORM\Column(name="http_status", type="smallint")
     */
    private $http_status = 0;

    /**
     * @var string $home_url
     *
     * @ORM\Column(name="home_url", type="string", length=255, nullable=true)
     */
    private $home_url;

    /**
     * @var string $api_regex
     *
     * @ORM\Column(name="api_regex", type="string", length=255, nullable=true)
     */
    private $api_regex;

    /**
     * @var \Rts\AppMonBundle\Entity\Server
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="apps")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id", nullable=true)
     */
    private $server;

    /**
     * @var \Rts\AppMonBundle\Entity\AppCategory
     * @ORM\ManyToOne(targetEntity="AppCategory", inversedBy="apps")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true)
     */
    private $category;


    /**
     * constructor
     *
     * @throws \Exception if CURL is not installed/configured
     */
    public function __construct()
    {
        // curl is required
        if (!function_exists('curl_init')) {
            throw new \Exception('You need to enable curl in your php.ini!');
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        if (!empty($name)) {
            $this->name = $name;
        }
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set api_url
     *
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->api_url = $apiUrl;
    }

    /**
     * Get api_url
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->api_url;
    }

    /**
     * Set meta_data_json
     *
     * @param text $metaDataJson
     */
    public function setMetaDataJson($metaDataJson)
    {
        $this->meta_data_json = $metaDataJson;
    }

    /**
     * Get meta_data_json
     *
     * @return text
     */
    public function getMetaDataJson()
    {
        return $this->meta_data_json;
    }

    /**
     * get meta data
     *
     * @return array|null if data could not be parsed as json
     */
    public function getMetaData()
    {
        $data = json_decode($this->getMetaDataJson(), true);

        return $data;
    }

    /**
     * Set version
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set http_status
     *
     * @param smallint $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->http_status = $httpStatus;
    }

    /**
     * Get http_status
     *
     * @return smallint
     */
    public function getHttpStatus()
    {
        return $this->http_status;
    }

    /**
     * Set home_url
     *
     * @param string $homeUrl
     */
    public function setHomeUrl($homeUrl)
    {
        $this->home_url = $homeUrl;
    }

    /**
     * Get home_url
     *
     * @return string
     */
    public function getHomeUrl()
    {
        $homeUrl = empty($this->home_url) ? $this->getApiUrl() : $this->home_url;
        return $homeUrl;
    }

    /**
     * Set api_regex
     *
     * @param string $apiRegex
     */
    public function setApiRegex($apiRegex)
    {
        $this->api_regex = $apiRegex;
    }

    /**
     * Get api_regex
     *
     * @return string
     */
    public function getApiRegex()
    {
        return $this->api_regex;
    }

    /**
     * Set server
     *
     * @param Rts\Bundle\AppMonBundle\Entity\Server $server
     */
    public function setServer(\Rts\Bundle\AppMonBundle\Entity\Server $server = NULL)
    {
        $this->server = $server;
    }

    /**
     * Get server
     *
     * @return Rts\Bundle\AppMonBundle\Entity\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * set data of the entity based
     * on the value in array $data
     *
     * @param array $data
     * @return \Rts\Bundle\AppMonBundle\Entity\App
     */
    public function setData(array $data)
    {
        if (array_key_exists('name', $data)) {
            $this->setName($data['name']);
        }

        if (array_key_exists('meta_data_json', $data)) {
            $this->setMetaDataJson(json_encode($data['meta_data_json']));
        }

        if (array_key_exists('version', $data)) {
            if (!empty($data['version'])) {
                $this->setVersion($data['version']);
            }
        }

        if (array_key_exists('http_status', $data)) {
            $this->setHttpStatus($data['http_status']);
        }

        return $this;
    }

    /**
     * get the app data from server
     * TODO: wrap cUrl methods in class
     *
     * @param string $url the url to retrieve data from
     * @throws \Exception if data could not be retrieved from server
     * @return array|mixed
     */
    public function getDataFromServer($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $contents = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Could not retrieve data from server because: ' . curl_error($ch));
        }

        $info = curl_getinfo($ch);

        if (!empty($contents)) {
            $data = json_decode($contents, true);
        }

        if (NULL == $data && !empty($contents)) {
            $data = array();

            // find version
            $pattern = $this->getApiRegex();
            $data['version'] = null;
            if (!empty($pattern)) {
                // try to use regex to retrieve version information from raw content
                $matches = array();
                preg_match($pattern, $contents, $matches);
                $version = isset($matches[1]) ? $matches[1] : NULL;
                $data['version'] = $version;
            }

            $crawler = new Crawler();
            $crawler->addContent($contents);

            try {
                $data['name'] = $crawler->filter('title')->text();
             } catch (\Exception $e) {
            }

        }

        // add http status
        $data['http_status'] = isset($info['http_code']) ? $info['http_code'] : 0;

        return $data;
    }

    /**
     * get server hostname by reverse-dns-lookup based
     * on the IP found for this App's server location
     *
     * @param $url
     * @return string
     */
    public function getServerHostnameByUrl($url)
    {
        $ipAddress = $this->getServerIpAddressByUrl($url);
        return gethostbyaddr($ipAddress);
    }

    /**
     * get server IP address by App's URL location
     * @param $url
     * @return string
     */
    public function getServerIpAddressByUrl($url)
    {
        $hostname = parse_url($url, PHP_URL_HOST);
        return gethostbyname($hostname);
    }


    /**
     * update app record by retrieving
     * information from server
     *
     * @throws \Exception
     * @return \Rts\Bundle\AppMonBundle\Entity\App
     */
    public function updateFromServer()
    {
        $data = $this->getDataFromServer(
            $this->getApiUrl()
        );

        // special case, do not overwrite name
        // if name is already set on this object
        if (!empty($this->name) && array_key_exists('name', $data)) {
            unset($data['name']);
        }

        $this->setData($data);

        return $this;
    }

    /**
     * return (summary) of data in array format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'status' => $this->getHttpStatus(),
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() . ' (#' . $this->getId() . ')';
    }

    /**
     * @param \Rts\AppMonBundle\Entity\AppCategory $category
     */
    public function setCategory($category = NULL)
    {
        $this->category = $category;
    }

    /**
     * @return \Rts\AppMonBundle\Entity\AppCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $meta_local_data
     */
    public function setMetaLocalData($meta_local_data)
    {
        $this->meta_local_data = $meta_local_data;
    }

    /**
     * @return array
     */
    public function getMetaLocalDataArray()
    {
        $data = array();

        // split on ;
        $items = explode(';', $this->meta_local_data);

        foreach ($items as $item) {
            @list ($key, $value) = explode('=', $item);
            $key = str_replace("\"", "", $key);
            $value = str_replace("\"", "", $value);

            if ('' != $key) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @return \Rts\Bundle\AppMonBundle\Entity\text
     */
    public function getMetaLocalData()
    {
        return $this->meta_local_data;
    }

}