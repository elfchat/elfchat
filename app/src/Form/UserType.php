<?php
/* (c) Anton Medvedev <anton@elfet.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElfChat\Form;

use ElfChat\Form\Transformer\UserTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    public function __construct(UserTransformer $userTransformer)
    {
        $this->userTransformer = $userTransformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->userTransformer);
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'user';
    }
} 