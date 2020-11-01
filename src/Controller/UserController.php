<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;


class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user_id", methods={"GET"})
     */
    public function userId(User $id): Response
    {
        return $this->json(
            $id->toArray(),
        );
    }

    /**
     * @Route("/user", name="user_info", methods={"GET"})
     */
    public function getInfoUser(): Response
    {
        $user = $this->getUser();

        return $this->json(
            $user->toArray(),
        );
    }

    /**
     * @Route("/user", name="user_delete", methods={"DELETE"})
     */
    public function deleteUserAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // force manual logout of logged in user    
        $this->get('security.token_storage')->setToken(null);

        $em->remove($user);
        $em->flush();

        return $this->json([
            'user' => "deleted",
        ]);
    }

    /**
     * @Route("/user", name="new_user", methods={"POST"})
     */
    public function newUser(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $firstname = $request->get('firstname');
        $lastname = $request->get('lastname');
        $email = $request->get('email');
        $password = $request->get('password');
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

        if (!isset($firstname) || $firstname === null || $firstname === '') {
           return $this->json([
                'errors' => "invalid firstname format",
            ]);
        }

        if (!isset($lastname) || $lastname === null || $lastname === '') {
            return $this->json([
                'errors' => "invalid lastname format",
             ]);
        }

        if (preg_match($pattern, $email) !== 1) {
            return $this->json([
                'errors' => "invalid email format",
            ]);
        }

        $manager = $this->getDoctrine()->getManager();

        $u = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array('email' => $email));

        if ($u !== null) {
            return $this->json([
                'errors' => "Mail already exist",
            ]);
        }

        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);

        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);
    
        $manager->persist($user);
        $manager->flush();

        return $this->json(
            $user->toArray(),
        );
    }

    /**
     * @Route("/user", name="user_edit", methods={"PUT"})
     */
    public function editUser(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->getUser();

        $firstname = $request->get('firstname');
        $lastname = $request->get('lastname');
        $email = $request->get('email');
        $password = $request->get('password');
        $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

        if (!isset($firstname) || $firstname === null || $firstname === '') {
            return $this->json([
                 'errors' => "invalid firstname format",
             ]);
         }
 
         if (!isset($lastname) || $lastname === null || $lastname === '') {
             return $this->json([
                 'errors' => "invalid lastname format",
              ]);
         }
 
         if (preg_match($pattern, $email) !== 1) {
             return $this->json([
                 'errors' => "invalid email format",
             ]);
         }
 
         $manager = $this->getDoctrine()->getManager();

         $u = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array('email' => $email));

        if ($u !== null && $this->getUser($email)) {
            return $this->json([
                'errors' => "Mail not changed",
            ]);
        }
 
        $manager = $this->getDoctrine()->getManager();

        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setEmail($email);

        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);
    
        $manager->persist($user);
        $manager->flush();

        return $this->json(
            $user->toArray(),
        );
    }
}
