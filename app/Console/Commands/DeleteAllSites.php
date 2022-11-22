<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DeleteAllSites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:delete-all-sites';

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
        //For testing clean first for new run
        
        $serverFromId = config('ploi.to_server');

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

                    dump('Try to delete ' . data_get($data,'domain'));

                    $this->deleteSite($serverFromId, data_get($data,'id'));
                }
            }
        }
    }

    public function deleteSite(int $serverId, int $siteId)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->delete(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId);

        if($request->status() == 402) {


        }
    }
}
