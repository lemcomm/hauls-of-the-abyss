<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

class Character {
	private ?string $name = null;
	private ?bool $alive = null;
	private ?bool $retired = null;
	private ?DateTimeInterface $retiredOn = null;
	private ?DateTimeInterface $created = null;
	private ?DateTimeInterface $lastAccess = null;
	private ?bool $slumbering = null;
	private ?bool $special = null;
	private ?int $wounded = null;
	private ?int $id = null;
	private Collection $userLogs;
	private ?User $user = null;
	private ?Race $race = null;
	private ?int $areaCode = null;
	private ?string $gender = null;
	private Collection $descriptions;
	private ?Description $description = null;
	private Collection $updatedDescriptions;
	private Collection $journals;
	private ?Origin $origin = null;
	private ?Room $room = null;
	private ?Room $lastRoom = null;
	private ?float $energy = null;
	private ?float $maxEnergy = null;
	private Collection $skills;
	private ?Floor $floor = null;
	private Collection $teleporters;
	private ?Dungeon $dungeon = null;

	public function __construct() {
   		$this->userLogs = new ArrayCollection();
   		$this->descriptions = new ArrayCollection();
   		$this->updatedDescriptions = new ArrayCollection();
   		$this->journals = new ArrayCollection();
   		$this->skills = new ArrayCollection();
   		$this->teleporters = new ArrayCollection();
   	}

	public function findSkill(SkillType $skill) {
   		foreach ($this->skills as $each) {
   			if ($each->getType() === $skill) {
   				return $each;
   			}
   		}
   		return false;
   	}

	public function findSkillByName(string $name) {
   		foreach ($this->skills as $each) {
   			if ($each->getType()->getName() === $name) {
   				return $each;
   			}
   		}
   		return false;
   	}

	public function isActive($slumbererIsActive = true): bool {
   		if (!$this->alive) {
   			return false;
   		}
   		if ($this->retired) {
   			return false;
   		}
   		if (!$slumbererIsActive && $this->slumbering) {
   			return false;
   		}
   		return true;
   	}

	/*
	 * Automatically generated functions follow.
	 */

	public function getName(): ?string {
   		return $this->name;
   	}

	public function setName(string $name): static {
   		$this->name = $name;
   
   		return $this;
   	}

	public function isAlive(): ?bool {
   		return $this->alive;
   	}

	public function setAlive(bool $alive): static {
   		$this->alive = $alive;
   
   		return $this;
   	}

	public function isRetired(): ?bool {
   		return $this->retired;
   	}

	public function setRetired(?bool $retired): static {
   		$this->retired = $retired;
   
   		return $this;
   	}

	public function getRetiredOn(): ?DateTimeInterface {
   		return $this->retiredOn;
   	}

	public function setRetiredOn(?DateTimeInterface $retiredOn): static {
   		$this->retiredOn = $retiredOn;
   
   		return $this;
   	}

	public function getCreated(): ?DateTimeInterface {
   		return $this->created;
   	}

	public function setCreated(DateTimeInterface $created): static {
   		$this->created = $created;
   
   		return $this;
   	}

	public function getLastAccess(): ?DateTimeInterface {
   		return $this->lastAccess;
   	}

	public function setLastAccess(DateTimeInterface $lastAccess): static {
   		$this->lastAccess = $lastAccess;
   
   		return $this;
   	}

	public function isSlumbering(): ?bool {
   		return $this->slumbering;
   	}

	public function setSlumbering(bool $slumbering): static {
   		$this->slumbering = $slumbering;
   
   		return $this;
   	}

	public function isSpecial(): ?bool {
   		return $this->special;
   	}

	public function setSpecial(bool $special): static {
   		$this->special = $special;
   
   		return $this;
   	}

	public function getWounded(): ?int {
   		return $this->wounded;
   	}

	public function setWounded(int $wounded): static {
   		$this->wounded = $wounded;
   
   		return $this;
   	}

	public function getId(): ?int {
   		return $this->id;
   	}

	/**
	 * @return Collection<int, UserLog>
	 */
	public function getUserLogs(): Collection {
   		return $this->userLogs;
   	}

	public function addUserLog(UserLog $userLog): static {
   		if (!$this->userLogs->contains($userLog)) {
   			$this->userLogs->add($userLog);
   			$userLog->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeUserLog(UserLog $userLog): static {
   		if ($this->userLogs->removeElement($userLog)) {
   			// set the owning side to null (unless already changed)
   			if ($userLog->getCharacter() === $this) {
   				$userLog->setCharacter(null);
   			}
   		}
   
   		return $this;
   	}

	public function getUser(): ?User {
   		return $this->user;
   	}

	public function setUser(?User $user): static {
   		$this->user = $user;
   
   		return $this;
   	}

	public function getRace(): ?Race {
   		return $this->race;
   	}

	public function setRace(?Race $race): static {
   		$this->race = $race;
   
   		return $this;
   	}

	public function getAreaCode(): ?int {
   		return $this->areaCode;
   	}

	public function setAreaCode(int $areaCode): static {
   		$this->areaCode = $areaCode;
   
   		return $this;
   	}

	public function getGender(): ?string {
   		return $this->gender;
   	}

	public function setGender(string $gender): static {
   		$this->gender = $gender;
   
   		return $this;
   	}

	public function getDescription(): ?Description {
   		return $this->description;
   	}

	public function setDescription(?Description $description): static {
   		// unset the owning side of the relation if necessary
   		if ($description === null && $this->description !== null) {
   			$this->description->setActiveCharacter(null);
   		}
   
   		// set the owning side of the relation if necessary
   		if ($description !== null && $description->getActiveUser() !== $this) {
   			$description->setActiveCharacter($this);
   		}
   
   		$this->description = $description;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Description>
	 */
	public function getDescriptions(): Collection {
   		return $this->descriptions;
   	}

	public function addDescription(Description $description): static {
   		if (!$this->descriptions->contains($description)) {
   			$this->descriptions->add($description);
   			$description->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeDescription(Description $description): static {
   		if ($this->descriptions->removeElement($description)) {
   			// set the owning side to null (unless already changed)
   			if ($description->getCharacter() === $this) {
   				$description->setCharacter(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Description>
	 */
	public function getUpdatedDescriptions(): Collection {
   		return $this->updatedDescriptions;
   	}

	public function addUpdatedDescription(Description $updatedDescription): static {
   		if (!$this->updatedDescriptions->contains($updatedDescription)) {
   			$this->updatedDescriptions->add($updatedDescription);
   			$updatedDescription->setUpdater($this);
   		}
   
   		return $this;
   	}

	public function removeUpdatedDescription(Description $updatedDescription): static {
   		if ($this->updatedDescriptions->removeElement($updatedDescription)) {
   			// set the owning side to null (unless already changed)
   			if ($updatedDescription->getUpdater() === $this) {
   				$updatedDescription->setUpdater(null);
   			}
   		}
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Journal>
	 */
	public function getJournals(): Collection {
   		return $this->journals;
   	}

	public function addJournal(Journal $journal): static {
   		if (!$this->journals->contains($journal)) {
   			$this->journals->add($journal);
   			$journal->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeJournal(Journal $journal): static {
   		if ($this->journals->removeElement($journal)) {
   			// set the owning side to null (unless already changed)
   			if ($journal->getCharacter() === $this) {
   				$journal->setCharacter(null);
   			}
   		}
   
   		return $this;
   	}

	public function getOrigin(): ?Origin {
   		return $this->origin;
   	}

	public function setOrigin(?Origin $origin): static {
   		$this->origin = $origin;
   
   		return $this;
   	}

	public function getRoom(): ?Room {
   		return $this->room;
   	}

	public function setRoom(?Room $room): static {
   		$this->room = $room;
   
   		return $this;
   	}

	public function getEnergy(): ?float {
   		return $this->energy;
   	}

	public function setEnergy(float $energy): static {
   		$this->energy = $energy;
   
   		return $this;
   	}

	public function getMaxEnergy(): ?float {
   		return $this->maxEnergy;
   	}

	public function setMaxEnergy(float $maxEnergy): static {
   		$this->maxEnergy = $maxEnergy;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, Skill>
	 */
	public function getSkills(): Collection {
   		return $this->skills;
   	}

	public function addSkill(Skill $skill): static {
   		if (!$this->skills->contains($skill)) {
   			$this->skills->add($skill);
   			$skill->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeSkill(Skill $skill): static {
   		if ($this->skills->removeElement($skill)) {
   			// set the owning side to null (unless already changed)
   			if ($skill->getCharacter() === $this) {
   				$skill->setCharacter(null);
   			}
   		}
   
   		return $this;
   	}

	public function getLastRoom(): ?Room {
   		return $this->lastRoom;
   	}

	public function setLastRoom(?Room $lastRoom): static {
   		$this->lastRoom = $lastRoom;
   
   		return $this;
   	}

	/**
	 * @return Collection<int, CharacterTeleporter>
	 */
	public function getTeleporters(): Collection {
   		return $this->teleporters;
   	}

	public function addTeleporter(CharacterTeleporter $teleporter): static {
   		if (!$this->teleporters->contains($teleporter)) {
   			$this->teleporters->add($teleporter);
   			$teleporter->setCharacter($this);
   		}
   
   		return $this;
   	}

	public function removeTeleporter(CharacterTeleporter $teleporter): static {
   		if ($this->teleporters->removeElement($teleporter)) {
   			// set the owning side to null (unless already changed)
   			if ($teleporter->getCharacter() === $this) {
   				$teleporter->setCharacter(null);
   			}
   		}
   
   		return $this;
   	}

	public function getDungeon(): ?Dungeon {
   		return $this->dungeon;
   	}

	public function setDungeon(?Dungeon $dungeon): static {
   		$this->dungeon = $dungeon;
   
   		return $this;
   	}

	public function getFloor(): ?Floor {
   		return $this->floor;
   	}

	public function setFloor(?Floor $floor): static {
   		$this->floor = $floor;
   
   		return $this;
   	}
}
