package com.rajschool.repository;

import com.rajschool.model.Institution;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.util.Optional;

@Repository
public interface InstitutionRepository extends JpaRepository<Institution, Long> {
    Optional<Institution> findByEmisCode(String emisCode);

    Optional<Institution> findByEmail(String email);
}
