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
        $serverFromId = config('ploi.from_server');
        $serverToId = config('ploi.to_server');

        $sites = $this->sites($serverFromId);
        if($sites) {
            foreach($sites as $site) {

                $certificates = $this->certificates($serverFromId, data_get($site,'id'));
                $repository = $this->repository($serverFromId, data_get($site,'id'));
                $env = $this->env($serverFromId, data_get($site,'id'));
                $deploy = $this->deploy($serverFromId, data_get($site,'id'));

                dump('Try to create ' . data_get($site,'domain'));

                $site['root_domain'] = data_get($site,'domain');

                $newSite = $this->createSite($serverToId, $site);
                if($newSite) {

                    sleep(5);

                    $this->updateSitePhp($serverToId, data_get($newSite,'id'), [
                        'php_version' => data_get($site,'php_version'),
                    ]);

                    sleep(1);

                    if($repository) {

                        $this->createSiteRepo($serverToId, data_get($newSite, 'id'), [
                            'provider' => data_get($repository,'repository.provider'),
                            'branch' => data_get($repository,'repository.branch'),
                            'name' => data_get($repository,'repository.user') . '/' . data_get($repository,'repository.name'),
                        ]);
                    }

                    sleep(1);

                    if($certificates) {
                        foreach($certificates as $certificate) {

                            $certificate['certificate'] = data_get($certificate,'domain');
                            $certificate['force'] = true;

                            $this->createSiteCertificate($serverToId, data_get($newSite,'id'), $certificate);
                        }
                    }

                    sleep(1);

                    if($env) {

                        $this->updateSiteEnv($serverToId, data_get($newSite, 'id'), [
                            'content' => data_get($env,'deploy_script'),
                        ]);
                    }

                    sleep(1);

                    if($deploy) {

                        $this->updateSiteDeploy($serverToId, data_get($newSite, 'id'), [
                            'deploy_script' => $env,
                        ]);
                    }
                }

                exit;
            }
        }

    }

    public function sites(int $serverId)
    {
        $query = [
            'per_page' => 50
        ];

        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->get(config('ploi.url') . 'servers/' . $serverId . '/sites?' . http_build_query($query));
        if($request->status() == 200) {

            $response = $request->json();
            return data_get($response,'data');
        }
    }

    public function certificates(int $serverId, int $siteId)
    {
        $query = [];

        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->get(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/certificates?' . http_build_query($query));
        if($request->status() == 200) {

            $response = $request->json();
            return data_get($response,'data');
        }
    }

    public function repository(int $serverId, int $siteId)
    {
        $query = [];

        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->get(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/repository?' . http_build_query($query));
        if($request->status() == 200) {

            $response = $request->json();
            return data_get($response,'data');
        }
    }

    public function env(int $serverId, int $siteId)
    {
        $query = [];

        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->get(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/env?' . http_build_query($query));
        if($request->status() == 200) {

            $response = $request->json();
            return data_get($response,'data');
        }
    }

    public function deploy(int $serverId, int $siteId)
    {
        $query = [];

        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->get(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/deploy/script?' . http_build_query($query));

        if($request->status() == 200) {

            $response = $request->json();
            return data_get($response,'data');
        }
    }

    public function createSite(int $serverId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->post(config('ploi.url') . 'servers/' . $serverId . '/sites', $data);

        if($request->status() == 201) {

            $response = $request->json();

            return data_get($response,'data');
        }
    }

    public function updateSitePhp(int $serverId, int $siteId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->post(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/php-version', $data);

        if($request->status() == 200) {

            dump('Update site PHP version');
        }
    }

    public function updateSiteEnv(int $serverId, int $siteId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->patch(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/env', $data);

        if($request->status() == 200) {

            dump('Update site env file');
        }
    }

    public function updateSiteDeploy(int $serverId, int $siteId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->patch(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/deploy/script', $data);

        if($request->status() == 200) {

            dump('Update site deploy script');
        }
    }

    public function createSiteCertificate(int $serverId, int $siteId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->post(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/certificates', $data);

        if($request->status() == 201) {

            dump('Create site certificate');
        }
    }

    public function createSiteRepo(int $serverId, int $siteId, array $data)
    {
        $client = Http::acceptJson()
            ->withToken(config('ploi.key'));

        $request = $client->post(config('ploi.url') . 'servers/' . $serverId . '/sites/' . $siteId . '/repository', $data);

        if($request->status() == 200) {

            dump('Create site repo');
        }
    }
}
