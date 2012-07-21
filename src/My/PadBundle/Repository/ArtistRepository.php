<?php

namespace My\PadBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ArtistRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArtistRepository extends EntityRepository
{
	/** Return table size */
	public function getSize()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		return $qb->select($qb->expr()->count('a.id'))->from('My\PadBundle\Entity\Artist', 'a')
			->getQuery()
			->getSingleScalarResult();
	}


	/** Get json autocomplete string for jquery ui */
	public function getAutocomplete()
	{
		$artists = $this->findAll();
		if (!$artists) return;

		$list = array();
		foreach ($artists as $artist) {
			if (!in_array($artist->getName(), $list)) $list[] = json_encode($artist->getName());
		}
		return implode(',', $list);
	}


	/** Gets Ladder */
	public function getLadder($side, $limit=10)
	{
		if ($side == 'top') $order = 'desc';
		elseif ($side == 'low') $order = 'asc';
		else throw new \Exception('unknown side: ' . $side);

		$qb = $this->getEntityManager()->createQueryBuilder();
		return $qb->select('a')->from('My\PadBundle\Entity\Artist', 'a')
			->where('a.rating IS NOT NULL')
			->orderBy('a.rating', $order)
			->addOrderBy('a.rated', $order)
			->addOrderBy('a.modifiedAt', $order)
			->setMaxResults($limit)
			->getQuery()
			->getResult();
	}


	/** Clean table */
	public function clean()
	{
		$removed = 0;
		$artists = $this->findAll();
		if (count($artists)) foreach ($artists as $artist) {
			if ($artist->getSongs()->count() == 0) {
				$this->getEntityManager()->remove($artist);
				$removed++;
			}
		}
		return $removed;
	}


	/** Finds a random entity */
	public function getRandom($ignoreIds)
	{
		if (!is_array($ignoreIds)) $ignoreIds = array($ignoreIds);
		$size = $this->getSize() - count($ignoreIds) - 1;

		if ($size > 0) {

			$qb = $this->getEntityManager()->createQueryBuilder();
			return $qb->select('a')->from('My\PadBundle\Entity\Artist', 'a')
				->where($qb->expr()->notIn('a.id', '?1'))->setParameter(1, $ignoreIds)
				->setMaxResults(1)
				->setfirstResult(rand(0, $size))
				->getQuery()
				->getSingleResult();

		}

		return;
	}


	/**
	 * Get Top Artist To Add
	 */
	public function getTopArtistToAdd()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$artist = $qb->select('a')->from('My\PadBundle\Entity\Artist', 'a')
			->where('a.rating > ?1')->setParameter(1, 0)
			->andWhere($qb->expr()->orx(
				$qb->expr()->lt('a.fullAt', '?2'),
				'a.fullAt IS NULL'
			))
			->setParameter(2, new \DateTime())
			->orderBy('a.rating', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getSingleResult();

		$artistName = '<a href="http://en.wikipedia.org/wiki/Special:Search/' . str_replace(' ', '_', $artist->getName()) . '" target="_newtab">' . $artist->getName() . '</a>';
		$artistWait = '<a href="#" class="artistWait" artist="' . $artist->getId() . '">(full)</a>';
		return array(
				'tip' => 'Add more from ' . $artistName . ' ' . $artistWait,
				'entityId'=> null,
		);
	}

}