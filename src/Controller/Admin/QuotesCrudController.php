<?php

namespace App\Controller\Admin;

use App\Entity\Quotes;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuotesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Quotes::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
