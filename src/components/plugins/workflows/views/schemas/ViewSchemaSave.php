<?php
namespace extas\components\plugins\workflows\views\schemas;

use extas\components\dashboards\DashboardView;
use extas\components\dashboards\TDashboardChart;
use extas\components\exceptions\MissedOrUnknown;
use extas\components\plugins\Plugin;
use extas\components\plugins\workflows\views\TSchemaView;
use extas\components\workflows\entities\Entity;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\workflows\entities\IEntitySample;
use extas\interfaces\workflows\schemas\ISchema;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ViewSchemaSave
 *
 * @method IRepository workflowSchemas()
 * @method IRepository workflowEntitiesSamples()
 * @method IRepository workflowEntities()
 *
 * @stage view.schemas.save
 * @package extas\components\plugins\workflows\views
 * @author jeyroik@gmail.com
 */
class ViewSchemaSave extends Plugin
{
    use TDashboardChart;
    use TSchemaView;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @throws MissedOrUnknown
     */
    public function __invoke(RequestInterface $request, ResponseInterface &$response, array $args)
    {
        /**
         * @var $schemas ISchema[]
         */
        $schemaRepo = $this->workflowSchemas();
        $schemas = $schemaRepo->all([]);
        $itemsView = '';
        $itemView = new DashboardView([DashboardView::FIELD__VIEW_PATH => 'schemas/item']);
        $footer = '';
        $schemaName = $args['name'] ?? '';
        $schemaTitle = $_REQUEST['title'] ?? '';
        $schemaDesc = $_REQUEST['description'] ?? '';
        $schemaTransitions = $_REQUEST['transitions'] ?? '';
        $schemaEntity = $_REQUEST['entity_name'] ?? '';

        foreach ($schemas as $index => $schema) {
            if ($schema->getName() == $schemaName) {
                preg_match_all('/[^,\s]+/', $schemaTransitions, $matches);
                $schema
                    ->setTitle($schemaTitle)
                    ->setDescription($schemaDesc)
                    ->setEntityName($this->getEntityNameBySample($schemaEntity));
                $schemaRepo->update($schema);
            }
            $this->buildTransitions($schema, $itemView, $itemsView, $footer);
        }
        $this->renderPage($itemsView, $footer, $response);
    }

    /**
     * @param string $sampleName
     * @return string
     * @throws MissedOrUnknown
     */
    protected function getEntityNameBySample(string $sampleName): string
    {
        /**
         * @var IEntitySample $sample
         */
        $sample = $this->workflowEntitiesSamples()->one([IEntitySample::FIELD__NAME => $sampleName]);

        if (!$sample) {
            throw new MissedOrUnknown('entity sample "' . $sampleName . '"');
        }

        $entity = new Entity();
        $entity->buildFromSample($sample);

        $this->workflowEntities()->create($entity);

        return $entity->getName();
    }
}
