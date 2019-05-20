<?php

namespace App\DataFixtures;

use App\Entity\Pais;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PaisFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $paises=[
            ['pais'=>'Afganistán','capital'=>'Kabul','codigo'=>'+93'],
            ['pais'=>'Albania','capital'=>'Tirana','codigo'=>'+355'],
            ['pais'=>'Alemania','capital'=>'Berlín','codigo'=>'+21'],
            ['pais'=>'Andorra','capital'=>'Andorra la Vieja','codigo'=>'+684'],
            ['pais'=>'Angola','capital'=>'Luanda','codigo'=>'+376'],
            ['pais'=>'Antigua y Barbuda','capital'=>'Saint John','codigo'=>'+244'],
            ['pais'=>'Argentina','capital'=>'Buenos Aires','codigo'=>'+1264'],
            ['pais'=>'Armenia','capital'=>'Ereván','codigo'=>' +1268'],
            ['pais'=>'Australia','capital'=>'Canberra','codigo'=>'+54'],
            ['pais'=>'Austria','capital'=>'Viena','codigo'=>'+61'],
            ['pais'=>'Azerbaijan','capital'=>'Bakú','codigo'=>'+43'],
            ['pais'=>'Bahamas','capital'=>'Nasáu','codigo'=>'+1242'],
            ['pais'=>'Bangladesh','capital'=>'Daca','codigo'=>'+880'],
            ['pais'=>'Barbados','capital'=>'Bridgetown','codigo'=>'+1246'],
            ['pais'=>'Belize','capital'=>'Belmopán','codigo'=>'+501'],
            ['pais'=>'Benin','capital'=>'Porto-Novo','codigo'=>'+229'],
            ['pais'=>'Bolivia','capital'=>'Sucre','codigo'=>'+591'],
            ['pais'=>'Bosnia and Herzegovina','capital'=>'Sarajevo','codigo'=>'+387'],
            ['pais'=>'Botswana','capital'=>'Gaborone','codigo'=>'+267'],
            ['pais'=>'Brazil','capital'=>'Brasilia','codigo'=>'+55'],
            ['pais'=>'Brunei','capital'=>'Bandar Seri Begawan','codigo'=>'+673'],
            ['pais'=>'Bulgaria','capital'=>'','codigo'=>'+359'],
            ['pais'=>'Burkina Faso','capital'=>'Sofía','codigo'=>'+226'],
            ['pais'=>'Burundi','capital'=>'Buyumbura','codigo'=>'+257'],
            ['pais'=>'Cameroon','capital'=>'Yaundé','codigo'=>'+237'],
            ['pais'=>'Canada','capital'=>'Ottawa','codigo'=>'+001'],
            ['pais'=>'Chad','capital'=>'Yamena','codigo'=>'+235'],
            ['pais'=>'Chile','capital'=>'Santiago','codigo'=>'+56'],
            ['pais'=>'China','capital'=>'Pekín','codigo'=>'+86'],
            ['pais'=>'Colombia','capital'=>'Bogotá','codigo'=>'+672'],
            ['pais'=>'Comoros','capital'=>'Moroni','codigo'=>'+269'],
            ['pais'=>'Congo','capital'=>'Brazzaville','codigo'=>'+242'],
            ['pais'=>'Costa Rica','capital'=>'San José','codigo'=>'+506'],
            ['pais'=>'Croatia','capital'=>'Zagreb','codigo'=>'+385'],
            ['pais'=>'Cuba','capital'=>'La Habana','codigo'=>'+53'],
            ['pais'=>'Denmark','capital'=>'Copenhague','codigo'=>'+45'],
            ['pais'=>'Dominica','capital'=>'Roseau','codigo'=>'+1767'],
            ['pais'=>'Ecuador','capital'=>'Quito','codigo'=>'+593'],
            ['pais'=>'Egypt','capital'=>'El Cairo','codigo'=>'+20'],
            ['pais'=>'El Salvador','capital'=>'San Salvador','codigo'=>'+503'],
            ['pais'=>'Estonia','capital'=>'Tallin','codigo'=>'+372'],
            ['pais'=>'Ethiopia','capital'=>'Adís Abeba','codigo'=>'+251'],
            ['pais'=>'Fiji','capital'=>'Suva','codigo'=>'+679'],
            ['pais'=>'Finland','capital'=>'Helsinki','codigo'=>'+358'],
            ['pais'=>'France','capital'=>'París','codigo'=>'+33'],
            ['pais'=>'Gabon','capital'=>'Libreville','codigo'=>'+241'],
            ['pais'=>'Gambia, The','capital'=>'Banjul','codigo'=>'+220'],
            ['pais'=>'Georgia','capital'=>'Tiflis','codigo'=>'+995'],
            ['pais'=>'Ghana','capital'=>'Accra','codigo'=>'+233'],
            ['pais'=>'Grecia','capital'=>'Atenas','codigo'=>'+30'],
            ['pais'=>'Grenada','capital'=>'Saint George','codigo'=>'+299'],
            ['pais'=>'Guatemala','capital'=>'Guatemala','codigo'=>'+502'],
            ['pais'=>'Guinea','capital'=>'Conakri','codigo'=>'+224'],
            ['pais'=>'Guinea-Bissau','capital'=>'Malabo','codigo'=>'+245'],
            ['pais'=>'Guyana','capital'=>'Georgetown','codigo'=>'+592'],
            ['pais'=>'Haiti','capital'=>'Puerto Príncipe','codigo'=>'+509'],
            ['pais'=>'Honduras','capital'=>'Tegucigalpa','codigo'=>'+504'],
            ['pais'=>'Hungary','capital'=>'Budapest','codigo'=>'+36'],
            ['pais'=>'India','capital'=>'Nueva Delhi','codigo'=>'+91'],
            ['pais'=>'Indonesia','capital'=>'Yakarta','codigo'=>'+62'],
            ['pais'=>'Iran','capital'=>'Teherán','codigo'=>'+98'],
            ['pais'=>'Iraq','capital'=>'Bagdad','codigo'=>'+964'],
            ['pais'=>'Israel','capital'=>'Jerusalén','codigo'=>'+972'],
            ['pais'=>'Italy','capital'=>'Roma','codigo'=>'+39'],
            ['pais'=>'Jamaica','capital'=>'Kingston','codigo'=>'+1876'],
            ['pais'=>'Japan','capital'=>'Tokio','codigo'=>'+81'],
            ['pais'=>'Kiribati','capital'=>'Tarawa','codigo'=>'+686'],
            ['pais'=>'Kuwait','capital'=>'Kuwait City','codigo'=>'+965'],
            ['pais'=>'Laos','capital'=>'Vientián','codigo'=>'+856'],
            ['pais'=>'Liberia','capital'=>'Monrovia','codigo'=>'+231'],
            ['pais'=>'Libya','capital'=>'Trípoli','codigo'=>'+21'],
            ['pais'=>'Liechtenstein','capital'=>'Vaduz','codigo'=>'+41'],
            ['pais'=>'Lithuania','capital'=>'Vilna','codigo'=>'+370'],
            ['pais'=>'Luxembourg','capital'=>'Luxemburgo','codigo'=>'+352'],
            ['pais'=>'Macedonia','capital'=>'Skopie','codigo'=>'+389'],
            ['pais'=>'Madagascar','capital'=>'Antananarivo','codigo'=>'+261'],
            ['pais'=>'Maldives','capital'=>'Malé','codigo'=>'+960'],
            ['pais'=>'Mali','capital'=>'Bamako','codigo'=>'+223'],
            ['pais'=>'Malta','capital'=>'La Valeta','codigo'=>'+356'],
            ['pais'=>'Mauritania','capital'=>'Nuakchot','codigo'=>'+222'],
            ['pais'=>'Mexico','capital'=>'México','codigo'=>'+1706'],
            ['pais'=>'Micronesia','capital'=>'Palikir','codigo'=>'+691'],
            ['pais'=>'Monaco','capital'=>'Mónaco','codigo'=>'+33'],
            ['pais'=>'Mongolia','capital'=>'Ulán Bator','codigo'=>'+976'],
            ['pais'=>'Namibia','capital'=>'Windhoek','codigo'=>'+264'],
            ['pais'=>'Nauru','capital'=>'Yaren','codigo'=>'+674'],
            ['pais'=>'Nicaragua','capital'=>'Managua','codigo'=>'+505'],
            ['pais'=>'Niger','capital'=>'Niamey','codigo'=>'+227'],
            ['pais'=>'Nigeria','capital'=>'Abuya','codigo'=>'+234'],
            ['pais'=>'Oman','capital'=>'Mascate','codigo'=>'+968'],
            ['pais'=>'Pakistan','capital'=>'Islamabad','codigo'=>'+92'],
            ['pais'=>'Panama','capital'=>'Panamá','codigo'=>'+507'],
            ['pais'=>'Papua New Guinea','capital'=>'Puerto Moresby','codigo'=>'+675'],
            ['pais'=>'Paraguay','capital'=>'Asunción','codigo'=>'+595'],
            ['pais'=>'Peru','capital'=>'Lima','codigo'=>'+51'],
            ['pais'=>'Poland','capital'=>'Varsovia','codigo'=>'+48'],
            ['pais'=>'Portugal','capital'=>'Lisboa','codigo'=>'+351'],
            ['pais'=>'Russia','capital'=>'Moscú','codigo'=>'+7'],
            ['pais'=>'San Marino','capital'=>'San Marino','codigo'=>'+39'],
            ['pais'=>'Senegal','capital'=>'Dakar','codigo'=>'+221'],
            ['pais'=>'Somalia','capital'=>'Mogadiscio','codigo'=>'+252'],
            ['pais'=>'Spain','capital'=>'Madrid','codigo'=>'+34'],
            ['pais'=>'Sri Lanka','capital'=>'Sri Jayawardenapura Kotte','codigo'=>'+94'],
            ];

        foreach ($paises as $it){
            $pais=new Pais();
            $pais->setNombre($it['pais']);
            $pais->setCapital($it['capital']);
            $pais->setCodigo($it['codigo']);
            $manager->persist($pais);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}
