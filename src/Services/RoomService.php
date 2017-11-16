<?php
namespace Go1\Services;

use GorkaLaucirica\HipchatAPIv2Client\Auth\AuthInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\MultipartStream;
use function GuzzleHttp\Psr7\stream_for;
use function GuzzleHttp\Psr7\modify_request;

class RoomService
{
    /** @var AuthInterface */
    protected $auth;

    /**
     * @var array
     */
    protected $config;

    protected $baseUrl;

    public function __construct(AuthInterface $auth, $config, $baseUrl = 'https://api.hipchat.com')
    {
        $this->auth = $auth;
        $this->baseUrl = $baseUrl;
        $this->config = $config;
    }

    /**
     * Share file with room
     * More info: https://www.hipchat.com/docs/apiv2/method/share_file_with_room
     *
     * @param string $id The id or name of the room
     * @param array $content Parameters be posted for example:
     *                              array(
     *                                'name'                => 'Example name',
     *                                'privacy'             => 'private',
     *                              )
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws
     */
    public function sharefileWithRoom($id, $file)
    {
        $url = $this->baseUrl . "/v2/room/{$id}/share/file";
        $headers = array(
            'Authorization' => $this->auth->getCredential()
        );

        $parts[] = [
            'headers' => [
                'Content-Type' => $file['file_type'] ?: 'application/octet-stream',
            ],
            'name' => 'file',
            'contents' => stream_for($file['content']),
            'filename' => $file['file_name'] ?: 'untitled',
        ];
        if (! empty($file['message'])) {
            $parts[] = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'name' => 'metadata',
                'contents' => json_encode(['message' => $file['message']]),
            ];
        }

        return $response =  $this->postMultipartRelated($url, [
                'headers' => $headers,
                'multipart' => $parts,
            ]);

    }

    /**
     * Make a multipart/related request.
     * Unfortunately Guzzle doesn't support multipart/related requests out of the box.
     *
     * @param $url
     * @param $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function postMultipartRelated($url, $options)
    {
        $headers = isset($options['headers']) ? $options['headers'] : [];
        $body = new MultipartStream($options['multipart']);
        $version = isset($options['version']) ? $options['version'] : '1.1';
        $request = new Request('POST', $url, $headers, $body, $version);
        $changeContentType['set_headers']['Content-Type'] = 'multipart/related; boundary='.$request->getBody()->getBoundary();
        $request = modify_request($request, $changeContentType);
        $client = new HttpClient;
        return $client->send($request);
    }
}
