<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class SubscriptionService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return [
            'category_1' => 'Category 1',
            'category_2' => 'Category 2',
            'category_3' => 'Category 3',
        ];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = [];
        $file = fopen($this->params->get('subscriptions_location'), "r");

        while (!feof($file)) {
            $csv = fgetcsv($file);

            if ($csv) {
                $row['id'] = !empty($csv[0]) ? $csv[0] : '';
                $row['date'] = !empty($csv[1]) ? $csv[1] : '';
                $row['name'] = !empty($csv[2]) ? $csv[2] : '';
                $row['email'] = !empty($csv[3]) ? $csv[3] : '';
                $row['categories'] = !empty($csv[4]) ? explode('|', $csv[4]) : [];

                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function find(string $id): array
    {
        $subscriptions = $this->getData();

        foreach ($subscriptions as $key => $subscription) {
            if ($subscription['id'] === $id) {
                return $subscription;
            }
        }

        return [];
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function saveFormData(array $data): bool
    {
        $data['id'] = uniqid();
        $data['date'] = time();
        $csvLine = $this->arrayToCsvRow($data);

        try {
            $filesystem = new Filesystem();
            $filesystem->appendToFile($this->params->get('subscriptions_location'), $csvLine . PHP_EOL);

            return true;
        } catch (IOException $e) {
            dump($e);
            return false;
        }
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function editFormData(array $data): bool
    {
        $subscriptions = $this->getData();

        foreach ($subscriptions as $key => $subscription) {
            if ($subscription['id'] === $data['id']) {
                $subscriptions[$key] = $data;
            }
        }

        return $this->arrayToFile($subscriptions);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function delete(string $id): bool
    {
        $subscriptions = $this->getData();

        foreach ($subscriptions as $key => $subscription) {
            if ($subscription['id'] === $id) {
                unset($subscriptions[$key]);
            }
        }

        return $this->arrayToFile($subscriptions);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function arrayToCsvRow(array $data): string
    {
        $categories = implode('|', $data['categories']);
        return $data['id'] . ',' . $data['date'] . ',' . $data['name'] . ',' . $data['email'] . ',' . $categories;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function arrayToFile(array $data): bool
    {
        $csvData = '';

        foreach ($data as $subscription) {
            $csvData .= $this->arrayToCsvRow($subscription) . PHP_EOL;
        }

        try {
            $filesystem = new Filesystem();
            $filesystem->dumpFile($this->params->get('subscriptions_location'), $csvData);

            return true;
        } catch (IOException $e) {
            return false;
        }
    }
}
