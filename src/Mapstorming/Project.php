<?php
namespace Mapstorming;


use Mapstorming\Config\Config;

class Project {

    protected $config;

    public function __construct() {
        $this->config = new Config();
    }

    public function create($city, $layer, $properties) {
        $output = new \StdClass();

        $output->bounds = [
            $city->bounds->SWLng,
            $city->bounds->SWLat,
            $city->bounds->NELng,
            $city->bounds->NELat
        ];
        $output->center = [
            $city->mapConfig->centerLng,
            $city->mapConfig->centerLat,
            $city->mapConfig->centerZoom
        ];
        $output->format = "png";
        $output->interactivity = new \StdClass();
        $output->interactivity->layer = 'bk_' . $city->bkID . '_' . $layer;
        $output->interactivity->template_teaser = "";
        $output->interactivity->template_full = $this->propertiesToXml($properties);

        $output->minzoom = $city->mapConfig->minZoom;
        $output->maxzoom = $city->mapConfig->maxZoom;
        $output->srs = "+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0.0 +k=1.0 +units=m +nadgrids=@null +wktext +no_defs +over";
        $output->Stylesheet = ['style.mss'];
        $output->Layer = [];

        $l = new \StdClass();
        $l->geometry = $this->config->layerRender[$layer];
        $l->extent = $output->bounds;
        $l->status = 'Off';
        // Must be the same as name, will be the mapbox id
        $l->id = 'bk_' . $city->bkID . '_' . $layer;
        $l->class = $layer;
        $l->Datasource = new \StdClass();
        $l->Datasource->file = 'datasets/' . $city->bkID . '/bk_' . $city->bkID . '_' . $layer . '.geojson';
        $l->Datasource->id = $layer;
        $l->Datasource->project = '';
        $l->Datasource->srs = '';
        $l->{'srs-name'} = 'autodetect';
        $l->srs = '';
        $l->advanced = new \StdClass();
        // Must be the same as id, will be the mapbox id
        $l->name = 'bk_' . $city->bkID . '_' . $layer;
        array_push($output->Layer, $l);

        $output->scale = 2;
        $output->metatile = 2;
        $output->_basemap = "";
        $output->name = "";
        $output->description = "";
        $output->attribution = "";

        $this->save($output);

    }

    public function getJSON() {
        return json_decode(file_get_contents(__DIR__ . '/../../tilemill_project/template/project.mml'));
    }

    public function save($project) {
        file_put_contents(__DIR__ . '/../../tilemill_project/template/project.mml', json_encode($project));
    }

    private function propertiesToXml($properties) {
        $xml = "";
        foreach ($properties as $property) {
            $xml .= "<$property>{{".$property."}}</$property>";
        }
        return $xml;
    }
}