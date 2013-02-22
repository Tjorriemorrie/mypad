<?php

namespace My\PadBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RatingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RatingRepository extends EntityRepository
{
	/** Has Competed */
	public function hasCompeted($song1, $song2)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$result = $qb->select('r')->from('My\PadBundle\Entity\Rating', 'r')
			->where('r.winner = ?1')->setParameter(1, $song1->getId())
			->andWhere('r.loser = ?2')->setParameter(2, $song2->getId())
			->getQuery()
			->getResult();
		if (count($result) === 1) return true;

		$qb = $this->getEntityManager()->createQueryBuilder();
		$result = $qb->select('r')->from('My\PadBundle\Entity\Rating', 'r')
			->where('r.winner = ?1')->setParameter(1, $song2->getId())
			->andWhere('r.loser = ?2')->setParameter(2, $song1->getId())
			->getQuery()
			->getResult();
		if (count($result) === 1) return true;

		return false;
	}


	/** Retrieves Songs to Rate */
	public function getSongsToRate($current)
	{
		if ($current->getRated() >= max(5, $current->getPlaycount() * 3)) return null;
		$countSongs = $this->getEntityManager()->getRepository('MyPadBundle:Song')->getSize(true);

		//$avgRated = $this->getEntityManager()->getRepository('MyPadBundle:Song')->getHighestRated();
        $highestRatedSong = $this->getEntityManager()->getRepository('MyPadBundle:Song')->getHighestRated();
        $highestRated = max(1, $highestRatedSong->getRated());
        $ratedDecrement = $highestRated / $countSongs;

		//$avgRatedAt = $this->getEntityManager()->getRepository('MyPadBundle:Song')->getAverageRatedAt();
		$lastRatedAtSong = $this->getEntityManager()->getRepository('MyPadBundle:Song')->getLastRatedAt();
        $lastRatedAt = $lastRatedAtSong->getRatedAt();
        $diff = time() - $lastRatedAt->getTimestamp();
        $timeIncrement = max(1, $diff / $countSongs);

		$failCount = 0;
		do {
			$failCount++;
			if ($failCount > $countSongs) return;

			$lastRatedAt->modify('+' . round($timeIncrement) * 5 . ' seconds');
			$highestRated -= $ratedDecrement * 5;

			$qb = $this->getEntityManager()->createQueryBuilder();
			$song = $qb->select('s')->from('MyPadBundle:Song', 's')
				->setFirstResult(rand(0, ($countSongs - 1)))
				->setMaxResults(1)
				->getQuery()
				->getSingleResult();
		} while ($song->getId() == $current->getId() or
			$song->getPlaycount() < 1 or
			is_null($song->getTitle()) or
			is_null($song->getArtist()) or
			$song->getRatedAt() > $lastRatedAt or
			$song->getRated() > $highestRated or
			$this->hasCompeted($current, $song)
		);

		return $song;
	}


	/** Clean table */
	public function clean()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		return $qb->delete('My\PadBundle\Entity\Rating', 'r')
			->where('r.winner IS NULL')
			->orWhere('r.loser IS NULL')
			->getQuery()
			->getResult();
	}
}
