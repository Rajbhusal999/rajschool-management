package com.rajschool.controller;

import com.rajschool.model.Institution;
import com.rajschool.repository.InstitutionRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/institutions")
@CrossOrigin(origins = "http://localhost:5173")
public class InstitutionController {

    @Autowired
    private InstitutionRepository institutionRepository;

    @PostMapping("/register")
    public ResponseEntity<?> registerInstitution(@RequestBody Institution institution) {
        try {
            // Basic validation
            if (institutionRepository.findByEmisCode(institution.getEmisCode()).isPresent()) {
                return ResponseEntity.badRequest().body("EMIS Code already registered");
            }
            if (institutionRepository.findByEmail(institution.getEmail()).isPresent()) {
                return ResponseEntity.badRequest().body("Email already registered");
            }

            Institution savedInstitution = institutionRepository.save(institution);
            return ResponseEntity.ok(savedInstitution);
        } catch (Exception e) {
            return ResponseEntity.internalServerError().body("Error deploying portal: " + e.getMessage());
        }
    }
}
