<?php
namespace Go1\Services;

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

    public function __construct($config)
    {
        $this->config = $config;
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
     * @return array
     *
     * ```json
     * [
     *    [1, "Ca Kho",    25],
     *    [2, "Com Trang", 25],
     * ]
     * ```
     */
    public function getMenuData()
    {
        $sheetId = $this->config['spreadsheetId'];
        $readRange = $this->config['read_range'];

        $service = new Google_Service_Sheets($this->getClient());

        // read meta
        $sheets = $service->spreadsheets->get($sheetId)->getSheets();

        $firstSheet = $sheets[0];

        $range = "{$firstSheet->getProperties()->title}!{$readRange}";
        $response = $service->spreadsheets_values->get($sheetId, $range);
        $values = $response->getValues();

        return $values;
    }

    protected function mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
    {
        $diff = strlen( $input ) - mb_strlen( $input );
        return str_pad( $input, $pad_length + $diff, $pad_string, $pad_type );
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
        $html .=  $br . "\n";

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
}
