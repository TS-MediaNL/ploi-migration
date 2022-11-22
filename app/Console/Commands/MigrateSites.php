<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MigrateSites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-sites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $serverFromId = 31911;
        $serverToId = 43406;

        $query = [
            'per_page' => 50
        ];

        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->get(config('ploi.url') . 'servers/' . $serverFromId . '/sites?' . http_build_query($query));
        if($request->status() == 200) {

            $response = $request->json();

            if(data_get($response,'data')) {

                foreach(data_get($response,'data') as $data) {

                    dump('Try to create ' . data_get($data,'domain'));

                    $data['root_domain'] = data_get($data,'domain');

                    $this->createNewSite($serverToId, $data);
                }
            }
        }
    }

    public function createNewSite(int $serverId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->post(config('ploi.url') . 'servers/' . $serverId . '/sites', $data);

        if($request->status() == 201) {

            $response = $request->json();

            if(data_get($response,'data.php_version') != data_get($data,'php_version')) {

                dump('Update PHP version to ' . data_get($data,'php_version'));

                $this->updatePHP($serverId, data_get($response,'data.id'), [
                    'php_version' => data_get($data,'php_version'),
                ]);
            }
        }
    }

    public function updatePHP(int $serverId, int $siteId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->post(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/php-version', $data);

        if($request->status() == 200) {

        }
    }
}
