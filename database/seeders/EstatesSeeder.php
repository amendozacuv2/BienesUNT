<?php

namespace Database\Seeders;

use App\Models\Estate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class EstatesSeeder extends Seeder
{
    private const TOTAL_ESTATES = 5000000;

    private const BATCH_SIZE = 3000;

    /**
     * true  = limpia estates antes de generar la base de prueba.
     * false = intenta agregar otros 100000 bienes encima de los existentes.
     */
    private const TRUNCATE_BEFORE_SEED = true;

    public function run(): void
    {
        DB::disableQueryLog();

        if (self::TRUNCATE_BEFORE_SEED) {
            $this->truncateEstates();
        }

        $locations = $this->resolveLocations();
        $templates = $this->templates();
        $now = Carbon::now();
        $rows = [];
        $start = self::TRUNCATE_BEFORE_SEED
            ? 1
            : ((int) DB::table('estates')->max('id') + 1);

        for ($number = $start; $number < $start + self::TOTAL_ESTATES; $number++) {
            $template = $templates[($number - $start) % count($templates)];

            $rows[] = [
                'uuid' => (string) Str::uuid(),
                'location_id' => $locations[$this->locationKeyFor($template, $number)],
                'patrimonial_code' => $this->makePatrimonialCode($template['patrimonial_seed'], $number),
                'internal_code' => $this->makeInternalCode($number),
                'denomination' => $this->limit($template['denomination'], 255),
                'brand' => $this->limit($template['brand'], 150),
                'model' => $this->limit($template['model'], 150),
                'type' => $this->limit($template['type'], 150),
                'color' => $this->limit($template['color'], 100),
                'series' => $this->limit($template['series'], 150),
                'dimensions' => $template['dimensions'],
                'others' => $template['others'],
                'situation' => $template['situation'],
                'conservation_status' => $template['conservation_status'],
                'observation' => $template['observation'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === self::BATCH_SIZE) {
                DB::table('estates')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('estates')->insert($rows);
        }
    }

    private function truncateEstates(): void
    {
        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('auditable_type', Estate::class)
                ->delete();
        }

        DB::statement('TRUNCATE TABLE estates RESTART IDENTITY CASCADE');
    }

    /**
     * Usa solamente las areas y ubicaciones creadas por AreaLocationSeeder.
     */
    private function requiredLocations(): array
    {
        return [
            'INFORMATICA' => [
                'LABORATORIO 01',
                'LABORATORIO 02',
                'LABORATORIO 03',
            ],
            'GERENCIA MUNICIPAL' => [
                'PALACIO MUNICIPAL',
            ],
            'OFICINA DE ARCHIVO CENTRAL' => [
                'PALACIO MUNICIPAL (TERCER PISO)',
                'INMUEBLE ALQUILADO',
                'PALACIO MUNICIPAL (PRIMER PISO)',
            ],
        ];
    }

    private function resolveLocations(): array
    {
        $required = $this->requiredLocations();
        $areaNames = array_keys($required);
        $locationNames = collect($required)->flatten()->values()->all();

        $found = DB::table('locations')
            ->join('areas', 'areas.id', '=', 'locations.area_id')
            ->whereIn('areas.name', $areaNames)
            ->whereIn('locations.name', $locationNames)
            ->where('areas.is_active', true)
            ->where('locations.is_active', true)
            ->select([
                'locations.id',
                'areas.name as area_name',
                'locations.name as location_name',
            ])
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->area_name . '|' . $row->location_name => (int) $row->id,
            ])
            ->all();

        foreach ($required as $areaName => $locations) {
            foreach ($locations as $locationName) {
                $key = $areaName . '|' . $locationName;

                if (! isset($found[$key])) {
                    throw new RuntimeException(
                        "No existe o no esta activa la ubicacion requerida: {$key}. Ejecuta primero AreaLocationSeeder."
                    );
                }
            }
        }

        return $found;
    }

    private function locationKeyFor(array $template, int $number): string
    {
        $denomination = $template['denomination'];

        if (Str::contains($denomination, [
            'COMPUTADORA',
            'MONITOR',
            'IMPRESORA',
            'CPU',
            'TECLADO',
            'ROUTER',
            'SWITCH',
            'UPS',
            'ESTABILIZADOR',
            'PROYECCION',
            'MULTIFUNCIONAL',
            'PLOTTERS',
        ])) {
            return 'INFORMATICA|LABORATORIO ' . str_pad((string) ((($number - 1) % 3) + 1), 2, '0', STR_PAD_LEFT);
        }

        if (Str::contains($denomination, [
            'AMBULANCIA',
            'CAMION',
            'CAMIONETA',
            'MOTOCICLETA',
            'MOTO LINEAL',
            'TRACTOR',
            'CARGADOR',
            'MOTOFUMIGADORA',
            'SURCADOR',
            'ARADOS',
        ])) {
            return 'GERENCIA MUNICIPAL|PALACIO MUNICIPAL';
        }

        $archivo = [
            'OFICINA DE ARCHIVO CENTRAL|PALACIO MUNICIPAL (TERCER PISO)',
            'OFICINA DE ARCHIVO CENTRAL|INMUEBLE ALQUILADO',
            'OFICINA DE ARCHIVO CENTRAL|PALACIO MUNICIPAL (PRIMER PISO)',
        ];

        return $archivo[($number - 1) % count($archivo)];
    }

    private function makePatrimonialCode(?string $seed, int $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $seed);

        if (strlen($digits) >= 6) {
            $prefix = substr($digits, 0, 6);
        } else {
            $hash = (int) sprintf('%u', crc32((string) $seed));
            $prefix = (string) (($hash % 900000) + 100000);
        }

        return $prefix . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    private function makeInternalCode(int $number): string
    {
        return 'BIEN-' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    private function limit(?string $value, int $max): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function templates(): array
    {
        return array_map(fn ($row) => $this->template($row), [
            ['678200500002', 'AMBULANCIA', 'TOYOTA', 'HILUX', 'TIPO RURAL', 'BLANCO', '8AJFB8CB9K1557727', null, 'CABINA MEDICA', 'EN USO', 'REGULAR', null],
            ['042204310001', 'ARADOS EN GENERAL', 'FALMET', 'AR3-28', 'DE 03 DISCOS REVERSIBLES', 'NO APLICA', 'NO APLICA', null, 'NO APLICA', 'EN USO', 'REGULAR', null],
            ['746405920001', 'ARMARIO DE MADERA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON/CAOBA', 'NO APLICA', '179X90X41', null, 'EN USO', 'REGULAR', null],
            ['746406600001', 'ARMARIO DE METAL', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'PLOMO', 'NO APLICA', '179X90X45', null, 'EN USO', 'REGULAR', null],
            ['678209500002', 'CAMION (OTROS)', 'ISUZU', 'NPR75L-KL5VAYPEN', 'CAMION', 'ROJO BLANCO VERDE AZUL', 'JAANPR75KN7101077', null, 'CARROCERIA DE MADERA', 'EN USO', 'REGULAR', null],
            ['678245500001', 'CAMION VOLQUETE', 'HINO-700', 'ZS1EPV', 'PESADO - VOLQUETE', 'BLANCO VERDE', 'JHDZS1EP281S16952', null, null, 'EN USO', 'REGULAR', null],
            ['678250000001', 'CAMIONETA', 'TOYOTA', 'HILUX', 'PICK UP', 'TURQUEZA OSCURO MICA', 'MROFZ29GXF2576012', null, null, 'EN USO', 'REGULAR', null],
            ['740805000003', 'COMPUTADORA PERSONAL PORTATIL', 'DELL', 'NO APLICA', 'NO APLICA', 'PLOMO', '9NCYQ72', null, null, 'EN USO', 'REGULAR', null],
            ['952231860006', 'EQUIPO DE POSICIONAMIENTO - GPS', 'GARMIN', 'GPSMAP 67', 'NO APLICA', 'NEGRO/OLIVO', 'NO INDICA', null, null, 'EN USO', 'BUENO', null],
            ['742223580001', 'EQUIPO MULTIFUNCIONAL COPIADORA IMPRESORA SCANNER', 'HP', 'LASERJET 1536DNF MFP', 'NO APLICA', 'NEGRO', 'BRF5G1Q5W7', null, null, 'EN USO', 'BUENO', null],
            ['742223580010', 'EQUIPO MULTIFUNCIONAL COPIADORA IMPRESORA SCANNER Y/O FAX', 'EPSON', 'L4260', 'NO APLICA', 'NEGRO', 'XAEY016468', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['746437450007', 'ESCRITORIO DE MELAMINA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON', 'NO APLICA', '75X84X53', '01 CAJON Y 01 DIV. LADO DERECHO, TABLERO DESLIZABLE', 'EN USO', 'REGULAR', null],
            ['746437450001', 'ESCRITORIO DE MELAMINA (1 DIVISION)', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON CLARO', 'NO APLICA', '76X86X50', '02 DIV. LADO DERECHO, 01 TABLERO DESLIZABLE', 'EN USO', 'REGULAR', null],
            ['746437450005', 'ESCRITORIO DE MELAMINA (2 DIVISIONES Y 3 CAJONES)', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON', 'NO APLICA', '74X110X50', '03 CAJONES LADO DERECHO (FALTA 01)', 'EN USO', 'REGULAR', null],
            ['746437450008', 'ESCRITORIO DE MELAMINA (3 CAJONES)', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'PLOMO', 'NO APLICA', '76X120X60', '03 CAJONES LADO DERECHO, 02 DIV. LADO IZQ.', 'EN USO', 'REGULAR', null],
            ['462252150020', 'ESTABILIZADOR', 'FORZA', 'FVR-902', 'NO APLICA', 'NEGRO', '2403-18597833', null, null, 'EN USO', 'BUENO', null],
            ['602240520001', 'ESTACION TOTAL', 'NIKON', 'NIVO 5.M', 'NO APLICA', 'VERDE', 'NO INDICA', null, 'INCLUYE 02 PRISMAS (FALTA CARGADOR DE BATERIA)', 'EN USO', 'REGULAR', null],
            ['746441180004', 'ESTANTE DE MADERA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON', 'NO APLICA', null, 'ES DE MELAMINA', 'EN USO', 'REGULAR', null],
            ['746441520009', 'ESTANTE DE MELAMINA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'GRAFITO', 'NO APLICA', '200X92X30', '08 DIVISIONES', 'EN USO', 'REGULAR', null],
            ['746441520008', 'ESTANTE DE MELAMINA (10 DIVISIONES, 4 PUERTAS)', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON OSCURO', 'NO APLICA', '120X60X28', null, 'EN USO', 'REGULAR', null],
            ['746441520006', 'ESTANTE DE MELAMINA CON PUERTAS (10 DIVISIONES, 4 PUERTAS)', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON OSCURO', 'NO APLICA', '200X120X30', '08 DIV. 03 PUERTAS INFERIORES', 'EN USO', 'REGULAR', null],
            ['746441860001', 'ESTANTE DE MELAMINA CON PUERTAS (10 DIVISIONES,4 PUERTAS)', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON OSCURO', 'NO APLICA', '200X120X30', '08 DIV. 03 PUERTAS INFERIORES', 'EN USO', 'BUENO', null],
            ['746441860042', 'ESTANTE DE METAL', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'PLOMO/AZUL', 'NO APLICA', '240X115X30', '05 DIVISIONES', 'EN USO', 'BUENO', null],
            ['740850000001', 'IMPRESORA PARA PLANOS - PLOTTERS', 'HP', 'DESIGN JET T120', 'NO APLICA', 'NEGRO', 'CN8BAHMD1T', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['746449320001', 'MESA DE MADERA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON', 'NO APLICA', '76X60X40', '01 DIVISION', 'EN USO', 'REGULAR', null],
            ['746460850002', 'MODULO DE MADERA PARA MICROCOMPUTADORA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON', 'NO APLICA', '95X104X42', '06 DIVISIONES', 'EN USO', 'REGULAR', null],
            ['740877000002', 'MONITOR A COLOR', 'DELL', 'E2016H', 'NO APLICA', 'NEGRO', 'CN-0Y01GT-QDC0095A0TK1-A08', null, null, 'EN USO', 'REGULAR', null],
            ['740880370007', 'MONITOR LED', 'TEROS', 'TE-F240W', 'NO APLICA', 'NEGRO', 'TE24FHD82105M2535', null, null, 'EN USO', 'REGULAR', null],
            ['740880370009', 'MONITOR LED 32 IN', 'SAMSUNG', 'SMART', 'NO APLICA', 'NEGRO', '0DQUHCPT802329R', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['678268000007', 'MOTOCICLETA', 'CROSS', 'RADION 250', 'NO APLICA', 'ROJO/NEGRO', 'LHJYCMLA5NB420337', null, 'PARA USO DE LA RONDA CAMPESINA', 'EN USO', 'REGULAR', null],
            ['042265230001', 'MOTOFUMIGADORA', 'HONDA', 'WJR 4025', 'NO APLICA', 'BLANCO/ROJO/NEGRO', 'NO APLICA', null, 'NO APLICA', 'EN USO', 'REGULAR', null],
            ['042266180003', 'MOTOGUADAÑA', 'HONDA', 'GX35', null, 'ROJO/NEGRO/PLOMO', 'NO APLICA', null, null, 'EN USO', 'REGULAR', null],
            ['740892000001', 'SERVIDOR', 'LENOVO', 'THINKSERVER TS150', 'NO APLICA', 'NEGRO', 'M1D66886', null, null, 'EN USO', 'REGULAR', null],
            ['746481190002', 'SILLA FIJA DE MADERA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON', null, '101X40X42', 'ESPALDAR TALLADO, PATAS TORNEADAS, ASIENTO FORRADO CON TELA ROJA', 'EN USO', 'REGULAR', null],
            ['746481870001', 'SILLA FIJA DE METAL', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO', 'NO APLICA', '82X40X36', 'ASIENTO Y ESPALDAR DE COROFAN, PATAS CUADRADAS', 'EN USO', 'REGULAR', null],
            ['746482550005', 'SILLA FIJA DE OTRO MATERIAL', 'CEL', 'PAOLA', 'NO APLICA', 'BLANCO', '88X40X41', null, null, 'EN USO', 'REGULAR', null],
            ['746483900001', 'SILLA GIRATORIA DE METAL', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO', 'NO APLICA', '105X51X50', 'ESPALDAR ALTO DE MALLA Y COROFAN, ASIENTO DE MALLA', 'EN USO', 'REGULAR', null],
            ['746483900008', 'SILLA GIRATORIA DE METAL CON BRAZOS', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO', 'NO APLICA', '82X40X36', 'ESPALDAR ALTO DE MALLA Y COROFAN, ASIENTO DE MALLA', 'EN USO', 'REGULAR', null],
            ['952278340002', 'SISTEMA DE PROYECCION MULTIMEDIA', 'EPSON', 'FH52', 'NO APLICA', 'NEGRO', 'X8AD4100249', null, null, 'EN USO', 'REGULAR', null],
            ['740895000001', 'TECLADO - KEYBOARD', 'ADVANCE', 'E5XKB5137AU', 'NO APLICA', 'NEGRO', 'G2370005400', null, null, 'EN USO', 'REGULAR', null],
            ['673687990003', 'TRACTOR AGRICOLA', 'DEUTZ FAHR', '5100G', 'TRACTOR AGRICOLA CABINADO', 'VERDE', 'ZKDCX102W0TD10284', null, 'PROCEDENCIA ITALIANA', 'EN USO', 'REGULAR', null],
            ['673692590001', 'TRACTOR ORUGA', 'CATERPILLAR', 'D6T-XL', 'BULDOZER', 'AMARILLO', 'LAE00467', null, null, 'EN USO', 'REGULAR', null],
            ['740899500006', 'UNIDAD CENTRAL DE PROCESO - CPU', 'ADVANCE', 'OPEN VO5776', 'NO APLICA', 'NEGRO', 'GD120228660001', null, null, 'EN USO', 'REGULAR', null],
            ['112291580001', 'VENTILADOR ELECTRICO TIPO COLUMNA O TORRE DE 3 VELOCIDADES', 'EQUATION', 'TORRE', 'NO APLICA', 'NEGRO', '1135760025022201006610', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['746411180001', 'BANCA DE ASIENTOS MULTIPLES', 'NACIONAL', 'DE 04 CUERPOS', 'BUTACA', 'NEGRO', 'NO APLICA', '86X205X43', '04 ASIENTOS', 'EN USO', 'BUENO', null],
            ['746440330002', 'ESTANTE ARCHIVADOR DE MELAMINA', 'NACIONAL', 'NO APLICA', 'NO APLICA', 'CEREZO', 'NO APLICA', '35X120X200', '10 DIVISIONES', 'EN USO', 'BUENO', null],
            ['746452030001', 'MESA DE REUNIONES', 'NACIONAL', 'PARA 12 PERSONAS', 'NO APLICA', 'CEREZO', 'NO APLICA', '77X120X320', 'DE MELAMINA, CUADRADA', 'EN USO', 'BUENO', null],
            ['112271780001', 'TERMA', 'SOLE', 'SOLTEEL050C', 'NO APLICA', 'PLOMO', 'TEL5024013197', 'NO APLICA', null, 'EN USO', 'BUENO', null],
            ['676400150001', 'ARMARIO BASTIDOR METALICO - RACK CABINET', 'AMS', 'GABINETE', 'NO APLICA', 'NEGRO', 'NO APLICA', '163X60X100', '01 PUERTA DE VIDRIO', 'EN USO', 'BUENO', null],
            ['746488310001', 'SILLON GIRATORIO DE MATERIAL SINTETICO', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO', 'NO APLICA', null, 'ASIENTO Y ESPALDAR DE CUERINA, GARRUCHAS Y BASE DE PLASTICO', 'DESUSO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['COD. INT. 060', 'VITRINA DE METAL', 'NACIONAL', 'S/M', 'S/T', 'PLOMO', 'S/S', '176X100X34', '03 DIV. 02 PUERTAS SUPERIORES. LUNAS DESLIZABLES', 'DESUSO', 'REGULAR', null],
            ['746489330001', 'SILLON GIRATORIO DE METAL', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO/GRIS', null, '108X54X54', null, 'DESUSO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['COD. INT. 039', 'ESCRITORIO DE MADERA', 'NACIONAL', 'S/M', 'S/T', 'CAOBA', 'S/S', '79X120X57', '03 CAJONES LADO DERECHO', 'EN USO', 'REGULAR', null],
            ['746441180005', 'ESTANTE DE MADERA Y VIDRIO', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'MARRON OSCURO', 'NO APLICA', '155X201X39', '06 DIVISIONES', 'EN USO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['952285830001', 'TELEVISOR LCD', 'SONY', 'KV-21ME42C', 'NO APLICA', 'NEGRO', '4012969', null, null, 'DESUSO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['COD. INT. 005', 'MESITA DE MELAMINA', 'NACIONAL', 'S/M', 'S/T', 'CREMA/PLOMO', 'S/S', '59X64X60', 'PARA IMPRESORA Y/O FOTOCOPIADORA. 02 PUERTAS', 'EN USO', 'REGULAR', null],
            ['COD. INT. 010', 'VENTILADOR ELECTRICO TIPO COLUMNA O TORRE', 'AIR MONSTER', '15718', 'TORRE', 'NEGRO', 'S/S', null, null, 'EN USO', 'REGULAR', null],
            ['COD. INT. 012', 'ROUTER', 'XIAOMI', 'R4CM', 'S/T', 'BLANCO', '25091/A9UV30454', null, null, 'EN USO', 'REGULAR', null],
            ['COD. INT. 013', 'SWITCH PARA RED', 'TP-LINK', 'TL-SF100', null, 'BLANCO', '218875501975', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['COD. INT. 020', 'ACUMULADOR DE ENERGIA - EQUIPO DE UPS', 'FORZA', 'NT-512U', 'S/T', 'NEGRO', '230413502391', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['COD. INT. 083', 'TEODOLITO', 'TOPCON', 'DT-30', null, 'AMARILLO', 'T91531', 'NO APLICA', null, 'DESUSO', 'REGULAR', null],
            ['COD. INT. 084', 'LAMINADORA', 'AKILES', 'PRO-LAM PLUS 330', null, 'CREMA', '14090162', 'NO APLICA', null, 'EN USO', 'REGULAR', null],
            ['COD. INT. 085', 'PIZARRA ACRILICA', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'BLANCO', null, 'NO APLICA', 'FILO DE ALUMINIO', 'EN USO', 'REGULAR', null],
            ['COD. INT. 089', 'SURCADOR', 'NO INDICA', 'NO INDICA', null, 'AMARILLO', 'NO APLICA', 'NO APLICA', '03 BRAZOS', 'DESUSO', 'REGULAR', null],
            ['536425250001', 'CAMILLA DE METAL PORTATIL PLEGABLE', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO/BLANCO', 'NO APLICA', '182X60X75', 'CON COLCHONETA DE 3 CUERPOS', 'DESUSO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['678209500001', 'CAMION', 'MITSUBISHI FUSO', 'CANTER', 'NO APLICA', 'AZUL/BLANCO', 'JLBFE84DEHKU20223', null, null, 'EN USO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['678268000002', 'MOTO LINEAL', 'YAMAHA', 'AG200F', 'VEHICULO MENOR', 'AZUL', 'JYA33GX007CA131261', null, null, 'EN USO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
            ['112279700003', 'VENTILADOR ELECTRICO DE PIE X 18 IN', 'NO APLICA', 'NO APLICA', 'NO APLICA', 'NEGRO', 'NO APLICA', null, null, 'DESUSO', 'REGULAR', 'BIEN CON CÓDIGO PATRIMONIAL DADO DE BAJA QUE SIGUE SIENDO USADO Y/O ESTÁ EN LA ENTIDAD'],
        ]);
    }

    /**
     * Orden de columnas de cada plantilla:
     * patrimonial_seed, denomination, brand, model, type, color, series,
     * dimensions, others, situation, conservation_status, observation.
     */
    private function template(array $row): array
    {
        return [
            'patrimonial_seed' => $row[0],
            'denomination' => $row[1],
            'brand' => $row[2],
            'model' => $row[3],
            'type' => $row[4],
            'color' => $row[5],
            'series' => $row[6],
            'dimensions' => $row[7],
            'others' => $row[8],
            'situation' => in_array($row[9], Estate::SITUATIONS, true) ? $row[9] : Estate::SITUATIONS[0],
            'conservation_status' => in_array($row[10], Estate::CONSERVATION_STATUSES, true)
                ? $row[10]
                : Estate::CONSERVATION_STATUSES[1],
            'observation' => $row[11],
        ];
    }
}
