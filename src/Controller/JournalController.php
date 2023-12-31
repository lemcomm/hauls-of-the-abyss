<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\GuideKeeper;
use App\Entity\Journal;
use App\Entity\UserReport;
use App\Form\JournalType;
use App\Form\UserReportType;
use App\Service\AppState;
use App\Service\DiscordIntegrator;
use App\Service\GateKeeper;
use App\Service\NotificationManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class JournalController extends AbstractController {
	private AppState $app;
	private GateKeeper $gk;
	private EntityManagerInterface $em;
	private TranslatorInterface $trans;

	public function __construct(AppState $app, EntityManagerInterface $em, GateKeeper $gk, TranslatorInterface $trans) {
		$this->app = $app;
		$this->gk = $gk;
		$this->em = $em;
		$this->trans = $trans;
	}

	#[Route ('/journal/{id}', name: 'journal_read', requirements: ['id' => '\d+'])]
	public function journalAction(Journal $id): RedirectResponse|Response {
		$user = $this->app->security('journal_read', ['id'=>$id->getId()]);
		if ($user instanceof GuideKeeper) {
			$gm = false;
			$admin = false;
		} else {
			$gm = $this->isGranted('ROLE_DUNGEON_MASTER');
			$admin = $this->isGranted('ROLE_ADMIN');
		}
		$bypass = false;
		if (!$id->isPublic() && !$gm) {
			if (!$user || ($user && $user !== $id->getCharacter()->getUser())) {
				$this->addFlash('notice', "Sorry, but this journal entry is private, and you don't have access to it.");
				return $this->redirectToRoute('user_characters');
			}
		} elseif (!$id->isPublic() && $gm) {
			$bypass = true;
		}

		return $this->render('journal/view.html.twig', [
			'journal' => $id,
			'user' => $user,
			'gm' => $gm,
			'admin' => $admin,
			'bypass' => $bypass,
		]);
	}

	#[Route ('/journal/write', name: 'journal_write')]
	public function journalWriteAction(NotificationManager $note, Request $request): RedirectResponse|Response {
		$character = $this->gk->gateway('journal_write');
		if ($character instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($character->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($character->getRoute());
		}

		$form = $this->createForm(JournalType::class);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$journal = $this->newJournal($character, $data);

			$em = $this->em;
			$em->persist($journal);
			$em->flush();
			if ($journal->isPublic() && !$journal->isGraphic()) {
				$note->spoolJournal($journal);
			}
			$this->addFlash('notice', "Your journal has been written successfully!");
			return $this->redirectToRoute('maf_journal_mine');
		}

		return $this->render('journal/write.html.twig', [
			'form' => $form->createView(),
		]);
	}

	private function newJournal(Character $char, $data): Journal {
		$journal = new Journal;
		$journal->setCharacter($char);
		$journal->setDate(new DateTime('now'));
		$journal->setLanguage('English');
		$journal->setTopic($data['topic']);
		$journal->setEntry($data['entry']);
		$journal->setOoc($data['ooc']);
		$journal->setPublic($data['public']);
		$journal->setGraphic($data['graphic']);
		$journal->setPendingReview(false);
		$journal->setGMReviewed(false);
		$journal->setGMPrivate(false);
		$journal->setGMGraphic(false);
		return $journal;
	}

	#[Route ('/journal/mine', name: 'journal_mine')]
	public function journalMineAction(): RedirectResponse|Response {
		$character = $this->gk->gateway('journal_mine');
		if ($character instanceof GuideKeeper) {
			$this->addFlash('error', $this->trans->trans($character->getReason(), [], 'gatekeeper'));
			return new RedirectResponse($character->getRoute());
		}

		return $this->render('journal/mine.html.twig', [
			'char' => $character,
		]);
	}

	#[Route ('/journal/user/{id}', name: 'journal_character', requirements: ['id' => '\d+'])]
	public function journalCharacterAction(Character $id): Response {
		return $this->render('journal/user.html.twig', [
			'char' => $id,
		]);
	}

	#[Route ('/journal/report/{id}', name: 'journal_report', requirements: ['id' => '\d+'])]
	public function journalReportAction(DiscordIntegrator $discord, Request $request, Journal $id): RedirectResponse|Response {
		if ($this->isGranted('IS_AUTHENTICATED')) {
			$form = $this->createForm(UserReportType::class);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$em = $this->em;
				$user = $this->getUser();
				$report = new UserReport();
				$report->setUser($this->getUser());
				$report->setJournal($id);
				$report->setText($form->getData()['text']);
				$report->setType('Journal');
				$report->setDate(new DateTime('now'));
				if ($id->getPendingReview()) {
					$id->setPendingReview(true);
				}
				$em->persist($report);
				$em->flush();
				$text = $user->getUsername() . ' has reported the journal: [' . $id->getTopic() . '](https://hualsoftheabyss.com/journal/' . $id->getId() . ').';
				$discord->pushToDungeonMaster($text);
				$this->addFlash('notice', $this->trans->trans('journal.report.success', [], 'messages'));
				return $this->redirectToRoute('journal_read', ['id' => $id->getId()]);
			} else {
				return $this->render('Journal/report.html.twig', [
					'journal' => $id,
					'form' => $form->createView(),
				]);
			}
		} else {
			$this->addFlash('notice', $this->trans->trans('journal.report.failure', [], 'messages'));
			return $this->redirectToRoute('journal_read', ['id' => $id->getId()]);
		}
	}

	#[Route ('/journal/gmprivate/{id}', name: 'journal_gmprivate', requirements: ['id' => '\d+'])]
	public function journalGMPrivateAction(Journal $id): RedirectResponse {
		if ($this->isGranted('ROLE_DUNGEON_MASTER')) {
			$id->setGMPrivate(true);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('journal.gm.private.success', [], 'messages'));
		} else {
			$this->addFlash('notice', $this->trans->trans('journal.gm.private.failure', [], 'messages'));
		}
		return $this->redirectToRoute('journal_read', ['id' => $id->getId()]);
	}

	#[Route ('/journal/graphic/{id}', name: 'journal_gmgraphic', requirements: ['id' => '\d+'])]
	public function journalGMGraphicAction(Journal $id): RedirectResponse {
		if ($this->isGranted('ROLE_DUNGEON_MASTER')) {
			$id->setGMGraphic(true);
			$this->em->flush();
			$this->addFlash('notice', $this->trans->trans('journal.gm.graphic.success', [], 'messages'));
		} else {
			$this->addFlash('notice', $this->trans->trans('journal.gm.graphic.failure', [], 'messages'));
		}
		return $this->redirectToRoute('journal_read', ['id' => $id->getId()]);
	}

	#[Route ('/journal/gmremove/{id}', name: 'journal_gmremove', requirements: ['id' => '\d+'])]
	public function journalGMRemoveAction(Journal $id): RedirectResponse {
		if ($this->isGranted('ROLE_ADMIN')) {
			$em = $this->em;
			$em->remove($id);
			$em->flush();
			$this->addFlash('notice', $this->trans->trans('journal.gm.remove.success', [], 'messages'));
			return $this->redirectToRoute('maf_gm_pending');
		} else {
			$this->addFlash('notice', $this->trans->trans('journal.gm.remove.failure', [], 'messages'));
			return $this->redirectToRoute('public_index');
		}
	}
}
