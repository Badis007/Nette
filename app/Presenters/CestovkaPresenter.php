<?php

declare(strict_types=1);

namespace App\Presenters;
use Nette\Database\Context;
use Nette;
use Nette\Application\UI;
use Nette\Utils\DateTime;



 class CestovkaPresenter extends BasePresenter
{
	private $database;

	function __construct(Context $database){
		$this->database = $database;
	}

	public function renderDefault($order = 'id'){
		$this->template->zajezdy = $this->database
										->table('nabidka')
										->order($order)
										->fetchAll();
	}

	public function renderZaznam($id){
		$this->template->zajezd = $this->database
										->table('nabidka')
										->get($id);
	}

	public function actionDelete($id){
		$radek = $this->database->table('nabidka')
								->get($id);
		if($radek->delete()){
			$this->flashMessage('Záznam byl smazán','success');
		}
		else{
			$this->flashMessage('Došlo k chybě při mazání','danger');
		}
		$this->redirect("default");
	}

	protected function createComponentZajezdForm(): UI\Form{
		$form = new UI\Form;

		$form->addText('destinace','Místo pobytu: ')
				->setRequired(true);

		$form->addTextArea('popis','Popis: ')
				->setHtmlAttribute('rows','6');

		$form->addInteger('cena','Cena: ')
				->setDefaultValue(0)
				->addRule(UI\Form::MIN,'Hodnota nesmí být záporná!',0);

		$form->addText('datum','Začátek pobytu: ') //OŠETŘIT SPRÁVNÝ FORMÁT ZADÁNÍ 2017-06-14
				->setRequired(true);

		$form->addinteger('delka','Délka pobytu: ')
				->addRule(UI\Form::RANGE,'Hodnota musí být v rozsahu 1-14',[1,14])
				->setRequired(true);	

		$doprava = [
			'letadlo' => 'letadlo',
			'autobus' => 'autobus',
			'auto' => 'auto'
		];
		$form->addSelect('doprava','Doprava: ',$doprava);

		$form->addSubmit('submit','Potvrdit: ');

		$form->onSuccess[] = [$this, 'zajezdFormSucceeded'];
		return $form;
	}

	public function actionInsert(){
		$this['zajezdForm']['destinace'];
		$this['zajezdForm']['popis'];
		$this['zajezdForm']['cena'];
		$this['zajezdForm']['datum'];
		$this['zajezdForm']['delka'];
		$this['zajezdForm']['doprava'];
	}

	public function zajezdFormSucceeded(UI\Form $form, \stdClass $values):void{
		if ($id=$this->getParameter('id')) {
            $this->database->table('nabidka')
                    ->get($id)
                    ->update((array)$values);
            $this->flashMessage('Záznam byl aktualizován.');
        } else {
            $this->database->table('nabidka')->insert((array)$values);
            $this->flashMessage('Byl vložen nový záznam.');            
        }            
        $this->redirect('Cestovka:default');
    }

	public function renderUpdate($id){
		$data = $this->database->table('nabidka')->get($id);
		$data = $data->toArray();
		$this['zajezdForm']->setDefaults($data);
	}

}
