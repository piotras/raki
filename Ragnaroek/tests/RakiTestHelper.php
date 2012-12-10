<?php

class RakiTestHelper 
{
    const SG1Name = 'Raki SG1';
    const SG0TopicName = 'SG0 first topic';
    const SG1TopicName = 'Multilang';
    const LangEnName = 'Good';
    const LangFiName = 'Hyvä';
    const LangRuName = 'Xороший';

    public function getLangByCode($code)
    {
        $storage = new MidgardQueryStorage('midgard_language');
        $qs = new MidgardQuerySelect($storage);
        $qs->set_constraint(
            new midgard_query_constraint(
                new midgard_query_property('code'), 
                '=', 
                new midgard_query_value($code)
            )
        );
        $qs->execute();
        if ($qs->get_results_count() != 1) {
            throw new Exception("Failed to get language by '{$code}' code");
        }

        $langs = $qs->list_objects();
        return $langs[0];
    }
}

?>
