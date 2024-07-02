<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenApi\Generator;

class GenerateSwaggerDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger documentation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $openapi = Generator::scan([app_path()]);
        $path = public_path('swagger.json');
        file_put_contents($path, $openapi->toJson());
        $this->info("Swagger documentation generated at: {$path}");
    }
}
