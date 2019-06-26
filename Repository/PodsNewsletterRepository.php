<?php


class PodsNewsletterRepository
{
    private $pods;
    public function __construct()
    {
        $this->pods = pods('newsletter');

    }

    public function addSubscriber(string $email)
    {
        $data = [
            'newsletter' => $email
        ];

        $this->addPods($data);
    }

    public function existSubscriber(string $email): bool
    {
        $params = [
            'where'=>" email ='".$email."'"
        ];

        $podsResult = $this->pods->find($params);
        $this->pods->filters($params);

        if(empty($podsResult->fetch())) {
            return false;
        }

        return true;
    }

    private function addPods(array $data)
    {
       $this->pods->add($data);

    }

    public static function getOptionByNameGroupAndField(string $group,string $field)
    {
       $podsRepository =  pods( $group );
       return $podsRepository->field( $field );
    }

}