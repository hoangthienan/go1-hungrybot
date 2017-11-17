<?php

namespace Go1\Services;

use dawood\phpChrome\Chrome;
use Google_Client;
use Google_Service_Sheets;
use GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\Model\Message;

class GSheetService
{
    /**
     * @var array
     */
    protected $config;

    protected $client;

    protected $cacheFile;

    protected $cacheTimeout = 60;

    protected $orderFile;

    public function __construct($config)
    {
        $this->config = $config;

        $this->cacheFile = ROOT_DIR . '/cache/menu.json';
        $this->orderFile = ROOT_DIR . '/cache/order.json';
    }

    /**
     * Expands the home directory alias '~' to the full path.
     *
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    protected function expandHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }

        return str_replace('~', realpath($homeDirectory), $path);
    }


    /**
     * Returns an authorized API client.
     *
     * @return Google_Client the authorized client object
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new Google_Client();
            $this->client->setApplicationName(APPLICATION_NAME);
            $this->client->setScopes(explode(' ', SCOPES));
            $this->client->setAuthConfig(CLIENT_SECRET_PATH);
            $this->client->setAccessType('offline');

            // Load previously authorized credentials from a file.
            $credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
            if (file_exists($credentialsPath)) {
                $accessToken = json_decode(file_get_contents($credentialsPath), true);
            }
            else {
                // Request authorization from the user.
                $authUrl = $this->client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

                // Store the credentials to disk.
                if (!file_exists(dirname($credentialsPath))) {
                    mkdir(dirname($credentialsPath), 0700, true);
                }
                file_put_contents($credentialsPath, json_encode($accessToken));
                printf("Credentials saved to %s\n", $credentialsPath);
            }
            $this->client->setAccessToken($accessToken);

            // Refresh the token if it's expired.
            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                file_put_contents($credentialsPath, json_encode($this->client->getAccessToken()));
            }
        }

        return $this->client;
    }

    /**
     * @param bool $useCache
     * @return array ```json
     *
     * ```json
     * [
     * [1, "Ca Kho",    25],
     * [2, "Com Trang", 25],
     * ]
     * ```
     */
    public function getMenuData($useCache = true)
    {
        if ($useCache && file_exists($this->cacheFile) && filemtime($this->cacheFile) > time() - $this->cacheTimeout) {
            return json_decode(file_get_contents($this->cacheFile), true);
        }

        $sheetId = $this->config['spreadsheetId'];
        $readRange = $this->config['read_range'];

        $service = new Google_Service_Sheets($this->getClient());

        // read meta
        $sheets = $service->spreadsheets->get($sheetId)->getSheets();

        $firstSheet = $sheets[0];

        $range = "{$firstSheet->getProperties()->title}!{$readRange}";
        $response = $service->spreadsheets_values->get($sheetId, $range);
        $values = $response->getValues();

        file_put_contents($this->cacheFile, json_encode($values));

        return $values;
    }

    protected function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
    {
        $diff = strlen($input) - mb_strlen($input);

        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }

    /**
     * @return array|mixed
     *
     * [
     *   [fromId, itemId, name]
     *
     * ]
     */
    public function currentOrder()
    {
        $order = [];
        if (file_exists($this->orderFile)) {
            $order = json_decode(file_get_contents($this->orderFile), true);

            // fix empty order file
            if (!is_array($order)) {
                $order = [];
            }
        }

        return $order;
    }

    protected function writeOrder($order)
    {
        file_put_contents($this->orderFile, json_encode($order));

        // collect value to write sheet
        $data = [];
        foreach ($order as $item) {
            $found = false;
            foreach ($data as &$row) {
                if ($row[0] == $item[1]) {
                    $found = true;
                    $row[1][] = $item[2];
                    break;
                }
            }

            if (!$found) {
                $data[] = [$item[1], [ $item[2] ]];
            }
        }

        $this->writeData($data);
    }

    public function addOrder($fromId, $itemId, $name)
    {
        $order = $this->currentOrder();
        $isUpdated = false;
        foreach ($order as &$row) {
            if ($row[0] == $fromId) {
                $isUpdated = true;
                $row[1] = $itemId;
                $row[2] = $name;
            }
        }

        if (!$isUpdated) {
            $order[] = [$fromId, $itemId, $name];
        }

        $this->writeOrder($order);
    }

    public function reset()
    {
        @unlink($this->orderFile);
    }


    /**
     * @param array $data
     * ```json
     * [
     *   ["1", ["Phuong Huynh", "Vu Nguyen"]],
     *   ["3", "An Hoang"],
     *   ["5", "Chau Pham"]
     * ]
     * ```
     * @return \Google_Service_Sheets_UpdateValuesResponse
     */
    public function writeData($data)
    {
        $sheetId = $this->config['spreadsheetId'];
        $writeRange = $this->config['write_range'];

        $service = new Google_Service_Sheets($this->getClient());

        // read meta
        $sheets = $service->spreadsheets->get($sheetId)->getSheets();

        $writeSheet = $sheets[0];

        $menu = $this->getMenuData();

        $writeValues = [];

        foreach ($menu as $menuRow) {
            $writeRow = ["", ""];
            foreach ($data as $dataRow) {
                // match ID
                if ($dataRow[0] == $menuRow[0]) {
                    $writeRow[0] = count($dataRow[1]);
                    $writeRow[1] = implode("\n", $dataRow[1]);
                }
            }
            $writeValues[] = $writeRow;
        }

        $range = "{$writeSheet->getProperties()->title}!{$writeRange}";

        $body = new \Google_Service_Sheets_ValueRange(['values' => $writeValues]);
        $params = ['valueInputOption' => 'RAW'];

        return $service->spreadsheets_values->update($sheetId, $range, $body, $params);
    }

    public function getMenuRawText()
    {
        $menu = $this->getMenuData();

        // send to hipchat
        // $message = 'ready to get menu :D';
        // <p><img src="https://i.imgur.com/XksvoId.jpg"/></p>
        $html = '';

        $topTitle = 'Today Menu';

        $longestLineLen = 0;

        foreach ($menu as $menuRow) {
            $_len = mb_strlen($menuRow[1]);
            if ($_len > $longestLineLen) {
                $longestLineLen = $_len;
            }
        }

        foreach ($menu as $menuRow) {
            $line = ' #' . str_pad($menuRow[0], 4, ' ', STR_PAD_RIGHT);
            $line .= $this->mb_str_pad($menuRow[1], $longestLineLen, ' ', STR_PAD_RIGHT) . ' ';
            $line .= $menuRow[2];

            $html .= $line . "\n";
        }

        $lineLen = $longestLineLen + 10;
        $br = str_repeat('=', $lineLen);
        $html = str_pad($topTitle, $lineLen, '=', STR_PAD_BOTH) . "\n" . $html;
        $html .= $br . "\n";

        $html .= "Order from the menu
/order #số
/order #số [ghi chú khác]\n";
        $html .= $br;

        return $html;
    }

    public function sendMenu()
    {
        $html = $this->getMenuRawText();

        $html = "<pre>{$html}</pre>";

        $params = [
            'id'             => $this->config['roomId'],
            'from'           => '',
            'message'        => $html,
            'notify'         => true,
            'color'          => 'green',
            'message_format' => 'html',
            'date'           => null,
        ];
        $messageObj = new Message($params);

        $authToken = $this->config['authToken'];
        $auth = new OAuth2($authToken);
        $client = new Client($auth);
        $roomApi = new RoomAPI($client);
        $roomApi->sendRoomNotification($this->config['roomId'], $messageObj);
    }

    public function sendMenuImage()
    {
        $this->getMenuData(true);
        $this->takeMenuShot();
        $html = "<p><img src='{$this->config['base_url']}/images/menu2.jpg'></p>";

        $params = [
            'id'             => $this->config['roomId'],
            'from'           => '',
            'message'        => $html,
            'notify'         => true,
            'color'          => 'green',
            'message_format' => 'html',
            'date'           => null,
        ];
        $messageObj = new Message($params);

        $authToken = $this->config['authToken'];
        $auth = new OAuth2($authToken);
        $client = new Client($auth);
        $roomApi = new RoomAPI($client);
        $roomApi->sendRoomNotification($this->config['roomId'], $messageObj);
    }

    public function sendRoomMessage($text)
    {
        $params = [
            'id'             => $this->config['roomId'],
            'from'           => '',
            'message'        => $text,
            'notify'         => true,
            'color'          => 'green',
            'message_format' => 'text',
            'date'           => null,
        ];
        $messageObj = new Message($params);

        $authToken = $this->config['authToken'];
        $auth = new OAuth2($authToken);
        $client = new Client($auth);
        $roomApi = new RoomAPI($client);
        $roomApi->sendRoomNotification($this->config['roomId'], $messageObj);
    }

    public function processOrder($hookData)
    {
        $message = $hookData->item->message->message;
        $mention = $hookData->item->message->from->mention_name;

        // remove space
        $message = preg_replace('/\s+/', ' ', $message);

        // get id menu
        if (preg_match('/\/order\s[@#](\d+)(\s?.+)?/', $message, $match)) {
            $id = $match[1];

            $menu = $this->getMenuData();
            $found = false;
            foreach ($menu as $item) {
                if ($item[0] == $id) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $name = $hookData->item->message->from->name;
                if (count($match) > 2) {
                    $name .=': ' . $match[2];
                }

                $this->addOrder($hookData->item->message->from->id, $id, $name);
                $this->sendRoomMessage("@{$mention} success.");
            }
            else {
                $this->sendRoomMessage("@{$mention} order '{$id}' not found.");
            }
        }
        else {
            // not found ID
            $this->sendRoomMessage("@{$mention} invalid.");
        }
    }

    public function takeMenuShot()
    {
        $chrome = new Chrome($this->config['template_url'], $this->config['chrome_path']);
        $chrome->setArgument('--no-sandbox', '');
        $chrome->setOutputDirectory(ROOT_DIR.'/images');
        $chrome->setWindowSize(540, 640);
        $chrome->getScreenShot(ROOT_DIR.'/images/menu2.jpg');
    }
}
